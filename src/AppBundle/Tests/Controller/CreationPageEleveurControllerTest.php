<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:52
 */

namespace AppBundle\Tests\Security;


use AppBundle\Controller\CreationPageEleveurController;
use AppBundle\Entity\ERole;
use AppBundle\Service\PageEleveurService;
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
        $rand = rand();
        $nomElevage = 'Les Chartreux de Tatouine ' . $rand;
        $creationPageEleveurForm['form[nom]'] = $nomElevage;
        $client->submit($creationPageEleveurForm);

        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        $this->assertTrue($tokenStorage->getToken()->getUser()->hasRole(ERole::ELEVEUR));

        // Redirection vers sa page eleveur fraichement créé
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        $this->assertEquals('/les-chartreux-de-tatouine-' . $rand, $client->getRequest()->getRequestUri());
        $this->assertEquals('Bonjour '.$user->getUsername(), $client->request('GET', '/')->filter('h1')->text());
    }

    public function testAnonyme()
    {
        $client = static::createClient();
        $client->request('POST', '/', ['form' => ['nom' => 'testAnonyme']]);
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testDeuxUserMemePage()
    {
        $client = static::createClient();
        $pageEleveur1 = UserUtils::createNewEleveur($client, $this);

        // connexion avec un nouvel user
        UserUtils::create($client, $this);
        
        //le 2eme user utilise le meme nom que pageEleveur1
        $pageEleveurForm = $client->request('GET', '/')->filter('form')->form();
        $pageEleveurForm['form[nom]'] = $pageEleveur1->getNom();
        $client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
        $this->assertEquals('Une page eleveur du meme nom existe deja', $client->getResponse()->getContent());
    }

    public function testConvertionUrl()
    {
        // conservation des caractères de base
        $this->assertEquals('azertyuiopqsdfghjklmwxcvbn1234567890', PageEleveurService::convertToUrl('azertyuiopqsdfghjklmwxcvbn1234567890'));

        // trim
        $this->assertEquals('aaa', PageEleveurService::convertToUrl(' aaa '));

        // to lowercase
        $this->assertEquals('aaa', PageEleveurService::convertToUrl('AaA'));

        // suppression des caractères spéciaux
        $this->assertEquals('', PageEleveurService::convertToUrl('!?,.<>=&'));

        // remplacement des caractères convertibles
        $this->assertEquals('eureace', PageEleveurService::convertToUrl('€éàçè&'));

        // espaces convertis en dash
        $this->assertEquals('un-deux-trois', PageEleveurService::convertToUrl('un deux trois'));
    }
}