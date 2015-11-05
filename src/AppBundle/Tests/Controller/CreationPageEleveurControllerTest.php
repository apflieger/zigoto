<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:52
 */

namespace AppBundle\Tests\Security;


use AppBundle\Entity\ERole;
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

        $this->assertFalse($user->hasRole(ERole::ELEVEUR));
        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $client->submit($creationPageEleveurForm);


        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $this->assertTrue($tokenStorage->getToken()->getUser()->hasRole(ERole::ELEVEUR));

        // Redirection vers la home de l'éleveur
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertEquals('/', $client->getRequest()->getRequestUri());
        $this->assertEquals('Bonjour '.$user->getUsername(), $client->getCrawler()->filter('h1')->text());
    }
}