<?php

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\Photo;
use AppBundle\Service\PageAnimalService;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\TestTimeService;
use AppBundle\Tests\TestUtils;
use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PageAnimalControllerTest extends WebTestCase
{
    const FLAG_CONST_JS = 'flag:const-js';
    const FLAG_JS_EDITABLE = 'flag:js-editable';

    /** @var Client */
    private $client;

    /** @var TestUtils */
    private $testUtils;

    /** @var Serializer */
    private $serializer;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->serializer = $this->client->getContainer()->get('serializer');
        $this->testUtils = new TestUtils($this->client, $this);
    }

    public function test404()
    {
        $this->client->request('GET', '/animal/nonexisting-id');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testContent_Owner_PageVide()
    {
        /** @var TestTimeService $timeService */
        $timeService = $this->client->getContainer()->get('zigotoo.time');
        $timeService->lockNow();

        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());
        $this->assertContains($timeService->now()->format('d/m/Y'), $crawler->filter('#date-naissance')->text());
        $this->assertEquals(1, $crawler->filter('#description')->count());
        $this->assertEquals('Disponible', trim($crawler->filter('select#statut option[selected]')->text()));
        $this->assertEquals(1, $crawler->filter('#photo-drop')->count());

        // On vérifie qu'il y a un script qui passe l'id du commit au JS
        $script = $crawler->filter('script')->reduce(function (Crawler $script) {
            return strpos($script->text(), static::FLAG_CONST_JS);
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains($this->serializer->serialize($pageAnimal, 'json'), $script->text());
    }

    public function testContent_UserAnonyme_PageVide()
    {
        /** @var TestTimeService $timeService */
        $timeService = $this->client->getContainer()->get('zigotoo.time');
        $timeService->lockNow();

        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();
        $pageAnimal = $pageEleveur->getAnimaux()[0];

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertEquals(1, $crawler->filter('#nav-page-animal a[href="/' . $pageEleveur->getSlug() . '"]')->count());
        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());
        $this->assertContains($timeService->now()->format('d/m/Y'), $crawler->filter('#date-naissance')->text());
        $this->assertEquals(1, $crawler->filter('#description')->count());
        $this->assertEquals('Disponible', trim($crawler->filter('span#statut')->text()));
        $this->assertEquals(0, $crawler->filter('.photo')->count());
        $this->assertEquals(0, $crawler->filter('#photo-drop')->count());
    }

    public function testPageAnimalSupprime()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();
        $pageAnimal = $pageEleveur->getAnimaux()[0];

        $pageEleveur->setAnimaux([]);

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        $pageEleveurService->commit($this->testUtils->getUser(), $pageEleveur);

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertEquals(0, $crawler->filter('#nav-page-animal a')->count());
    }

    public function testAccesOwner()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertContains(self::FLAG_JS_EDITABLE, $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html());
    }

    public function testAccesUserNonOwner()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->logout();
        $this->testUtils->createUser();

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertNotContains(self::FLAG_JS_EDITABLE, $crawler->html());
    }

    public function testCommit_success()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $pageAnimal->setNom('Bernard');
        $pageAnimal->setDateNaissance(new \DateTime('2015/01/18'));
        $pageAnimal->setDescription('Un gros toutou');
        $pageAnimal->setStatut(PageAnimal::RESERVE);
        $photo = new Photo();
        $photo->setNom('img1.jpg');
        $pageAnimal->setPhotos([$photo]);

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        /** @var PageAnimalService $pageAnimalService */
        $pageAnimalService = $this->client->getContainer()->get('zigotoo.page_animal');

        $pageAnimal = $pageAnimalService->find($pageAnimal->getId());

        $this->assertEquals($this->serializer->serialize($pageAnimal, 'json'), $this->client->getResponse()->getContent());

        $this->testUtils->clearEntities();
        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());
        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());
        $this->assertContains('18/01/2015', $crawler->filter('#date-naissance')->text());
        $this->assertEquals('Un gros toutou', $crawler->filter('#description')->text());
        $this->assertEquals('Réservé', trim($crawler->filter('select#statut option[selected]')->text()));

        $this->testUtils->logout();
        $this->testUtils->clearEntities();
        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());
        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());
        $this->assertContains('18/01/2015', $crawler->filter('#date-naissance')->text());
        $this->assertEquals('Un gros toutou', $crawler->filter('#description')->text());
        $this->assertEquals('Réservé', trim($crawler->filter('#statut')->text()));
        $this->assertEquals(1, $crawler->filter('.photo')->count());
    }

    public function testCommit_logged_out()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->logout();

        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
    }

    public function testCommit_non_owner()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->createUser();

        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_NonFastForward()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        // 1er commit
        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // commit à partir du meme head de la page eleveur
        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json'));

        $this->assertEquals(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_BrancheInconnue()
    {
        $this->testUtils->createUser();

        $fakePageEleveur = new PageAnimal();
        $fakePageEleveur->setId(-1);
        $fakePageEleveur->setHead(-1);

        $this->client->request('POST', '/animal/1',
            array(), array(), array(),
            $this->serializer->serialize($fakePageEleveur, 'json')
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_empty_nom()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $pageAnimal->setNom('');

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertEquals(Response::HTTP_NOT_ACCEPTABLE, $this->client->getResponse()->getStatusCode());
    }

    public function testCommit_dateNaissance_nom()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $pageAnimal->setDateNaissance(null);

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/animal/' . $pageAnimal->getId(),
            array(), array(), array(),
            $this->serializer->serialize($pageAnimal, 'json')
        );

        $this->assertEquals(Response::HTTP_NOT_ACCEPTABLE, $this->client->getResponse()->getStatusCode());
    }
}