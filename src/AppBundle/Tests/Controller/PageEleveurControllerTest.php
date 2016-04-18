<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\PageEleveurController;
use AppBundle\Entity\Actualite;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\TestUtils;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PageEleveurControllerTest extends WebTestCase
{
    const FLAG_CONST_JS = 'flag:const-js';
    const FLAG_JS_EDITABLE = 'flag:js-editable';

    /** @var Serializer */
    private $serializer;
    /** @var Client */
    private $client;
    /** @var TestUtils */
    private $testUtils;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->testUtils = new TestUtils($this->client, $this);
        $this->serializer = $this->client->getContainer()->get('serializer');
    }

    public function test404()
    {
        $this->client->request('GET', '/nonexisting-eleveur');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testContent_Owner_PageVide()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals(1, $crawler->filter('#eleveur-toolbar #preview.btn')->count());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('#especes')->count());
        $this->assertEquals(1, $crawler->filter('#races')->count());
        $this->assertEquals(1, $crawler->filter('#lieu')->count());
        $this->assertEquals(1, $crawler->filter('#lieu')->count());
        $this->assertEquals(1, $crawler->filter('#description')->count());
        $this->assertEquals('Ajouter un animal', $crawler->filter('.animaux button')->text());
        $this->assertEquals('Actualités', $crawler->filter('#actualites h2')->text());
        $this->assertEquals('Nouvelle actualité', $crawler->filter('#actualites #ajouter-actualite')->text());

        // On vérifie que le JS est dans la page
        $script = $crawler->filter('script')->reduce(function (Crawler $script) {
            return strpos($script->text(), static::FLAG_CONST_JS);
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains($this->serializer->serialize($pageEleveur, 'json'), $script->text());
    }

    public function testContent_UserAnonyme_PageVide()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();
        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals(0, $crawler->filter('#eleveur-toolbar')->count());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEmpty($crawler->filter('#description')->text());
        $this->assertEquals(0, $crawler->filter('#especes')->count());
        $this->assertEquals(0, $crawler->filter('#races')->count());
        $this->assertEquals(0, $crawler->filter('#lieu')->count());
        $this->assertEquals(0, $crawler->filter('.animaux button')->count());
        $this->assertEquals(0, $crawler->filter('#actualites h2')->count());
        $this->assertEquals(0, $crawler->filter('#actualites #ajouter-actualite')->count());
    }

    public function testContent_UserAnonyme_PageComplete()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $pageEleveur->setDescription('nouvelle description');
        $pageEleveur->setEspeces('chats');
        $pageEleveur->setRaces('chartreux');

        /*
         * Enregistrement d'une actualité en base pour simuler le fait que
         * la page eleveur a déjà une actualité.
         */
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $actualite1 = new Actualite($this->getName() . rand(), new \DateTime('2015/12/25'));
        $entityManager->persist($actualite1);
        $entityManager->flush($actualite1);
        $this->testUtils->clearEntities();

        $pageEleveur->setActualites([$actualite1, new Actualite('Nouvelle portée', new \DateTime())]);

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        $pageEleveurService->commit(
            $this->testUtils->getUser(),
            $pageEleveur
        );

        $this->testUtils->logout();
        $this->testUtils->clearEntities();
        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals('nouvelle description', $crawler->filter('#description')->text());
        $this->assertEquals('chats', trim($crawler->filter('#especes')->text()));
        $this->assertEquals('chartreux', trim($crawler->filter('#races')->text()));
        $this->assertEquals(1, $crawler->filter('#actualites h2')->count());
        $this->assertEquals(2, $crawler->filter('#actualites .actualite')->count());
        $this->assertContains('Nouvelle portée', $crawler->filter('#actualites .actualite')->first()->text());
        $this->assertContains('25/12/2015', $crawler->filter('#actualites .actualite')->nextAll()->text());
        $this->assertContains($actualite1->getContenu(), $crawler->filter('#actualites .actualite')->nextAll()->text());
    }

    public function testCommit()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $pageEleveur->setNom('nouveau nom');
        $pageEleveur->setDescription('description non nulle');
        $pageEleveur->setActualites([new Actualite('Nouvelle portée', new \DateTime())]);

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        // mise à jour de la page eleveur après commit
        $pageEleveur = $pageEleveurService->findBySlug($pageEleveur->getSlug());

        $this->assertEquals($this->serializer->serialize($pageEleveur, 'json'), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());
        $this->assertEquals('nouveau nom', $crawler->filter('title')->text());
        $this->assertEquals('description non nulle', $crawler->filter('#description')->text());
    }

    public function testCommit_non_owner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // Connexion avec un autre user
        $this->testUtils->createUser();

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json'));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_logged_out()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // Simule une perte de session
        $this->testUtils->logout();

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json'));

        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
    }

    public function testAccesOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertContains(self::FLAG_JS_EDITABLE, $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html(), 'ca marche pas !');
    }

    public function testAccesUserNonOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();
        $this->testUtils->createUser();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html(), 'ca marche pas !');
    }

    public function testPreviewOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertContains(self::FLAG_JS_EDITABLE, $crawler->html());

        $previewLink = $crawler->filter('#eleveur-toolbar #preview')->link();

        $crawnlerPreview = $this->client->click($previewLink);

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawnlerPreview->html());
    }

    public function testPreviewAnonyme()
    {
        /*
         * Un user qui n'est pas owner de la page et qui arrive en ?preview
         * doit être redirigé sur l'url sans le param preview
         */
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();
        $this->testUtils->logout();

        $this->client->request('GET', '/' . $pageEleveur->getSlug() . '?preview');

        $this->assertTrue($this->client->getResponse()->isRedirect('/' . $pageEleveur->getSlug()));
    }

    public function testCommit_BrancheInconnue()
    {
        $this->testUtils->createUser();

        $fakePageEleveur = new PageEleveur();
        $fakePageEleveur->setId(-1);
        $fakePageEleveur->setHead(-1);

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($fakePageEleveur, 'json')
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_NonFastForward()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // 1er commit
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // commit à partir du meme head de la page eleveur
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json'));

        $this->assertEquals(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testAddAnimal_success()
    {
        $pageEleveur0 = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->client->request('POST', '/add-animal',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur0, 'json')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        /** @var PageEleveur $pageEleveur1 */
        $pageEleveur1 = $this->serializer->deserialize($this->client->getResponse()->getContent(), PageEleveur::class, 'json');

        $this->client->request('GET', '/animal/' . $pageEleveur1->getAnimaux()[0]->getId());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAddAnimal_droit_refuse()
    {
        $pageEleveur0 = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->createUser();

        $this->client->request('POST', '/add-animal',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur0, 'json')
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAnimal_thumbnail()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();
        $animal = $pageEleveur->getAnimaux()[0];

        $this->testUtils->logout();
        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals($animal->getNom(), $crawler->filter('a[href="/animal/'.$animal->getId().'"]')->text());
    }
}