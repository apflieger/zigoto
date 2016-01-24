<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\PageEleveurController;
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

    public function testContent()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $pageEleveur->setDescription('nouvelle description');

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigoto.page_eleveur');

        $pageEleveurService->commit(
            $this->testUtils->getUser(),
            $pageEleveur
        );

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals('nouvelle description', $crawler->filter('#description')->text());

        // On vérifie qu'il y a un script qui passe l'id du commit au JS
        $script = $crawler->filter('script')->reduce(function (Crawler $script) {
            return strpos($script->text(), 'const-js');
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains($this->serializer->serialize($pageEleveur, 'json'), $script->text());
    }

    public function testCommit()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $pageEleveur->setNom('nouveau nom');
        $pageEleveur->setDescription('description non nulle');

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json')
        );

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigoto.page_eleveur');

        // mise à jour de la page eleveur après commit
        $pageEleveur = $pageEleveurService->findBySlug($pageEleveur->getSlug());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertEquals($this->serializer->serialize($pageEleveur, 'json'), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());
        $this->assertEquals('nouveau nom', $crawler->filter('title')->text());
        $this->assertEquals('description non nulle', $crawler->filter('#description')->text());
    }

    public function testDroitCommitRefuse()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // Connexion avec un autre user
        $this->testUtils->createUser();

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json'));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAccesOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertContains('owner', $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertNotContains('owner', $crawler->html(), 'ca marche pas !');
    }

    public function testCommitBrancheInconnue()
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

    public function testCommitNonFastForward()
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
        $this->assertEquals('Ajouter un animal', $crawler->filter('.animaux button')->text());
    }
}