<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;
    /** @var TestUtils */
    private $testUtils;

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->testUtils = new TestUtils($this->client, $this);
    }

    public function testGetTeaser()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals('Créez votre site d\'éleveur', $crawler->filter('h1')->text());
        $this->assertEquals('Zigotoo - Créez votre site d\'éleveur', $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('meta[name="description"]')->count());
        $this->assertEquals(1, $crawler->filter('nav.global-header a[href="/"]')->count());
    }

    public function testLinkQuiSommesNous()
    {
        $crawler = $this->client->request('GET', '/');

        $this->client->click($crawler->filter('footer a[href="/qui-sommes-nous"]')->link());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testLinkContact()
    {
        $crawler = $this->client->request('GET', '/');

        $this->client->click($crawler->filter('footer a[href="/contact"]')->link());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testTeaserLoggedIn()
    {
        $user = $this->testUtils->createUser()->getUser();

        $crawler = $this->client->request('GET', '/');
        $this->assertContains('Bonjour ' . $user->getUsername(), $crawler->filter('body')->text());
        $this->assertEquals(2, $crawler->filter('a[href="/contact"]')->count());
    }
}
