<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 07/03/16
 * Time: 22:58
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
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

    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertEquals('Connexion Zigotoo', $crawler->filter('title')->text());
        $this->assertEquals('Connectez-vous à votre compte Zigotoo', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertEquals(1, $crawler->filter('form input[name="_username"]')->count());
    }

    public function testRegister()
    {
        $crawler = $this->client->request('GET', '/register/');

        $this->assertEquals('Création de compte Zigotoo', $crawler->filter('title')->text());
        $this->assertEquals('Créez votre compte sur Zigotoo', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertEquals(1, $crawler->filter('form input[name="fos_user_registration_form[email]"]')->count());
        $this->assertEquals(1, $crawler->filter('form input[name="fos_user_registration_form[username]"]')->count());
    }
}