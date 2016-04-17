<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 22:41
 */

namespace AppBundle\Tests\Controller;


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
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());

        // On vérifie qu'il y a un script qui passe l'id du commit au JS
        $script = $crawler->filter('script')->reduce(function (Crawler $script) {
            return strpos($script->text(), static::FLAG_CONST_JS);
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains($this->serializer->serialize($pageAnimal, 'json'), $script->text());
    }

    public function testContent_UserAnonyme_PageVide()
    {
        $pageAnimal = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur()->getAnimaux()[0];

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/animal/' . $pageAnimal->getId());

        $this->assertEquals($pageAnimal->getNom(), $crawler->filter('title')->text());

        
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
}