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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CreationPageEleveurControllerTest  extends WebTestCase
{
    public function testNouvelEleveur()
    {
        $client = static::createClient();
        $user = UserUtils::create($client, $this);

        // tant qu'il n'a pas créé sa page eleveur il n'a pas le ROLE_ELEVEUR
        $this->assertFalse($user->hasRole(ERole::ELEVEUR));

        // on va sur la home en mode connecté, il y a le formulaire de création de page eleveur
        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $nomElevage = 'elevage_' . $user->getUsername();
        $creationPageEleveurForm['elevage[nom]'] = $nomElevage;
        $client->submit($creationPageEleveurForm);

        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $this->assertTrue($tokenStorage->getToken()->getUser()->hasRole(ERole::ELEVEUR));

        // Redirection vers sa page eleveur fraichement créé
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertEquals('/' . $nomElevage, $client->getRequest()->getRequestUri());
        $this->assertEquals('Bonjour '.$user->getUsername(), $client->request('GET', '/')->filter('h1')->text());
    }

    public function testAnonyme()
    {
        $client = static::createClient();
        $client->request('POST', '/creation-page-eleveur', ['elevage' => ['nom' => 'testAnonyme']]);
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUnUserDeuxPages()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        $client->request('POST', '/creation-page-eleveur', ['elevage' => ['nom' => 'deuxiemePage']]);
        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
        $this->assertEquals('Vous avez deja une page eleveur', $client->getResponse()->getContent());
    }

    public function testDeuxUserMemePage()
    {
        $client = static::createClient();
        $pageEleveur1 = UserUtils::createNewEleveur($client, $this);

        $user2 = UserUtils::create($client, $this);
        
        //user2 utilise le meme nom que pageEleveur1
        $client->request('POST', '/creation-page-eleveur', ['elevage' => ['nom' => $pageEleveur1->getUrl()]]);

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
        $this->assertEquals('Une page eleveur du meme nom existe deja', $client->getResponse()->getContent());
    }
}