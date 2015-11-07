<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:52
 */

namespace AppBundle\Tests\Security;


use AppBundle\Entity\ERole;
use AppBundle\Tests\InjectClient;
use AppBundle\Tests\RequireLogin;
use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CreationPageEleveurControllerTest  extends WebTestCase
{
    public function testNouvelEleveur()
    {
        $client = static::createClient();
        $user = UserUtils::create($client, $this);

        $this->assertFalse($user->hasRole(ERole::ELEVEUR));

        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $nomElevage = 'pageEleveur_' . $user->getUsername();
        $creationPageEleveurForm['elevage[nom]'] = $nomElevage;
        $client->submit($creationPageEleveurForm);

        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $this->assertTrue($tokenStorage->getToken()->getUser()->hasRole(ERole::ELEVEUR));

        // Redirection vers la home de l'éleveur
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertEquals('/' . $nomElevage, $client->getRequest()->getRequestUri());
        $this->assertEquals('Bonjour '.$user->getUsername(), $client->request('GET', '/')->filter('h1')->text());
    }

    public function testAnonyme() {
        $client = static::createClient();
        $client->request('POST', '/creation-page-eleveur', ['elevage' => ['nom' => 'testAnonyme']]);
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }
}