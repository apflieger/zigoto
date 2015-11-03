<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:52
 */

namespace AppBundle\Tests\Security;


use AppBundle\Tests\UserUtils;
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

        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $client->submit($creationPageEleveurForm);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    public function testNouvelEleveur()
    {
        $client = static::createClient();
        $user = UserUtils::create($client, $this);

        $this->assertFalse($user->hasRole('ROLE_ELEVEUR'));
        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $client->submit($creationPageEleveurForm);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $this->assertTrue($tokenStorage->getToken()->getUser()->hasRole('ROLE_ELEVEUR'));
    }
}