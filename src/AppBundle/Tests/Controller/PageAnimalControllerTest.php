<?php

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\PageAnimal;
use AppBundle\Service\PageAnimalService;
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

        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());
        $this->assertContains($timeService->now()->format('d/m/Y'), $crawler->filter('#date-naissance')->text());
        $this->assertEquals(1, $crawler->filter('#description')->count());
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
}