<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 01/03/16
 * Time: 01:41
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class ContactControllerTest extends WebTestCase
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

    public function testAnonyme()
    {
        $crawler = $this->client->request('GET', '/contact');

        $this->assertEquals('Zigotoo - nous contacter', $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $this->assertEmpty($crawler->filter('form')->form()->get('form[email]')->getValue());
        $this->assertEmpty($crawler->filter('form')->form()->get('form[message]')->getValue());
    }

    public function testUserConnecte()
    {
        $this->testUtils->createUser();
        $crawler = $this->client->request('GET', '/contact');

        $this->assertEquals($this->testUtils->getUser()->getEmail(), $crawler->filter('form')->form()->get('form[email]')->getValue());
    }

    public function testEmailAsGetParam()
    {
        $this->testUtils->createUser();
        $crawler = $this->client->request('GET', '/contact?email=test@chmol.com');

        $this->assertEquals('test@chmol.com', $crawler->filter('form')->form()->get('form[email]')->getValue());
    }
}