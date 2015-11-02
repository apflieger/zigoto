<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:52
 */

namespace AppBundle\Tests\Security;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Tests\RequireLogin;
use AppBundle\Tests\InjectClient;

class CreationPageEleveurControllerTest  extends WebTestCase
{

    public function testNonConnecte()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $creationPageEleveurForm = $crawler->filter('#creation_page-eleveur')->form();
        $client->submit($creationPageEleveurForm);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    public function testNouvelEleveur()
    {

    }
}