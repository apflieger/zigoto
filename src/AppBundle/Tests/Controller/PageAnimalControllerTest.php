<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 22:41
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PageAnimalControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    /** @var TestUtils */
    private $testUtils;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->testUtils = new TestUtils($this->client, $this);
        $this->testUtils->createUser()->toEleveur()->addAnimal();
    }

    public function test404()
    {
        $this->client->request('GET', '/animal/nonexisting-id');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testContent()
    {
        $animal = $this->testUtils->getPageEleveur()->getCommit()->getAnimaux()[0];
        $crawler = $this->client->request('GET', '/animal/' . $animal->getId());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($animal->getNom(), $crawler->filter('title')->text());
    }
}