<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\Actualite;
use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\Photo;
use AppBundle\Service\PageAnimalService;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\TestTimeService;
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
        $this->client->request('GET', '/elevage/nonexisting-eleveur');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testContent_Owner_PageVide()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertEquals(1, $crawler->filter('#eleveur-toolbar #preview.btn')->count());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('#especes')->count());
        $this->assertEquals(1, $crawler->filter('#races')->count());
        $this->assertEquals(1, $crawler->filter('#lieu')->count());
        $this->assertEquals(1, $crawler->filter('#lieu')->count());
        $this->assertEquals(1, $crawler->filter('#description')->count());
        $this->assertEquals('Ajouter un animal', $crawler->filter('#ajout-animal')->text());
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

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

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
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();

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
        /* On doit avancer le temps pour que les actualités aient des createdAt différents
         parceque les requêtes font des orderBy createdAt */
        $actu = rand();
        $pageEleveur->setActualites([$actualite1, new Actualite($actu, new \DateTime())]);

        $pageAnimal = $pageEleveur->getAnimaux()[0];
        $photo = new Photo();
        $photo->setNom('portrait');
        $pageAnimal->setPhotos([$photo]);

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        /** @var PageAnimalService $pageAnimalService */
        $pageAnimalService = $this->client->getContainer()->get('zigotoo.page_animal');

        $pageEleveur = $pageEleveurService->commit($this->testUtils->getUser(), $pageEleveur);

        $pageAnimalService->commit($this->testUtils->getUser(), $pageAnimal);

        $this->testUtils->logout();
        $this->testUtils->clearEntities();
        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals('nouvelle description', $crawler->filter('#description')->text());
        $this->assertEquals('chats', trim($crawler->filter('#especes')->text()));
        $this->assertEquals('chartreux', trim($crawler->filter('#races')->text()));
        $this->assertEquals(1, $crawler->filter('#actualites h2')->count());
        $this->assertEquals(2, $crawler->filter('#actualites .actualite')->count());
        $this->assertContains($actu . '', $crawler->filter('#actualites .actualite')->first()->text());
        $this->assertContains('25/12/2015', $crawler->filter('#actualites .actualite')->nextAll()->text());
        $this->assertContains($actualite1->getContenu(), $crawler->filter('#actualites .actualite')->nextAll()->text());
        $this->assertContains($pageEleveur->getAnimaux()[0]->getNom(), $crawler->filter('#animaux .animal')->text());
        $this->assertContains($photo->getNom(), $crawler->filter('#animaux .animal img')->attr('src'));
        $this->assertContains('Disponible', $crawler->filter('#animaux .animal .statut-animal.chip-valid')->text());
    }

    public function testCommit_success()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $pageEleveur->setNom('nouveau nom');
        $pageEleveur->setDescription('description non nulle');
        $pageEleveur->setActualites([new Actualite(rand(), new \DateTime())]);

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

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());
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

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertContains(self::FLAG_JS_EDITABLE, $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html(), 'ca marche pas !');
    }

    public function testAccesUserNonOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();
        $this->testUtils->createUser();

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html(), 'ca marche pas !');
    }

    public function testPreviewOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

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

        $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug() . '?preview');

        $this->assertTrue($this->client->getResponse()->isRedirect('/elevage/' . $pageEleveur->getSlug()));
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

    public function testAddAnimal_droit_refuse()
    {
        $this->testUtils->createUser();
        $this->client->request('POST', '/animal');
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->serializer->deserialize($this->client->getResponse()->getContent(), PageAnimal::class, 'json');

        // un autre owner essaye d'ajouer la PA à sa PE
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();
        $pageEleveur->setAnimaux([$pageAnimal]);

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            $this->serializer->serialize($pageEleveur, 'json'));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAnimal_thumbnail()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();
        $animal = $pageEleveur->getAnimaux()[0];

        $this->testUtils->logout();
        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        $this->assertEquals($animal->getNom(), $crawler->filter('a[href="/animal/'.$animal->getId().'"]')->text());
    }

    public function testAnimalList() {
        /** @var TestTimeService $timeService */
        $timeService = $this->client->getContainer()->get('zigotoo.time');
        $timeService->lockNow();

        /*
         * On avance le temps pour que les createdAt des animaux diffèrent.
         * Si non le test est instable à cause du orderBy sur createdAt
         */
        $this->testUtils->createUser()->toEleveur()->addAnimal();
        $timeService->lockNow($timeService->now()->add(new \DateInterval('PT1M')));
        $this->testUtils->addAnimal();
        $timeService->lockNow($timeService->now()->add(new \DateInterval('PT1M')));
        $this->testUtils->addAnimal();
        $timeService->lockNow($timeService->now()->add(new \DateInterval('PT1M')));
        $this->testUtils->addAnimal();

        $pageEleveur = $this->testUtils->getPageEleveur();

        /*
         * A ce moment, les PA sont triés par createdAt puisque testUtils utilise
         * PageEleveurService->commit qui retourne une PE avec les PA triées
         */
        $animal0 = $pageEleveur->getAnimaux()[0];
        $animal1 = $pageEleveur->getAnimaux()[1];
        $animal2 = $pageEleveur->getAnimaux()[2];
        $animal3 = $pageEleveur->getAnimaux()[3];

        // On met un animal dans chaque statut
        $this->assertEquals(PageAnimal::DISPONIBLE, $animal0->getStatut());
        $animal1->setStatut(PageAnimal::OPTION);
        $animal2->setStatut(PageAnimal::RESERVE);
        $animal3->setStatut(PageAnimal::ADOPTE);

        /** @var PageAnimalService $pageAnimalService */
        $pageAnimalService = $this->client->getContainer()->get('zigotoo.page_animal');

        $pageAnimalService->commit($this->testUtils->getUser(), $animal1);
        $pageAnimalService->commit($this->testUtils->getUser(), $animal2);
        $pageAnimalService->commit($this->testUtils->getUser(), $animal3);

        $this->testUtils->logout();

        $this->testUtils->clearEntities();
        $crawler = $this->client->request('GET', '/elevage/' . $pageEleveur->getSlug());

        // En visiteur, on ne voit pas les animaux adoptés, on a donc que 3 animaux dans la page
        $this->assertEquals(3, $crawler->filter('.animaux .animal')->count());
        $this->assertContains($animal0->getId(), $crawler->filter('.animaux .animal:nth-child(1) a')->link()->getUri());
        $this->assertContains($animal1->getId(), $crawler->filter('.animaux .animal:nth-child(2) a')->link()->getUri());
        $this->assertContains($animal2->getId(), $crawler->filter('.animaux .animal:nth-child(3) a')->link()->getUri());
    }
}