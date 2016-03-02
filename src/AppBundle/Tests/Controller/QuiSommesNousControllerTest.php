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

class QuiSommesNousControllerTest extends WebTestCase
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

    public function testContent()
    {
        $crawler = $this->client->request('GET', '/qui-sommes-nous');

        $this->assertEquals('Zigotoo - qui sommes nous ?', $crawler->filter('title')->text());
    }
}