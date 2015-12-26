<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DefaultControllerTest extends WebTestCase
{
    public function testIndexAnonyme()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Créez votre page éleveur', $crawler->filter('h1')->text());
        $this->assertEquals(1, $crawler->filter('a[href="/login"]')->count());
    }

    public function testIndexNewEleveur()
    {
        $client = static::createClient();
        UserUtils::create($client, $this);

        $crawler = $client->request('GET', '/');

        // Quand l'utilisateur est connecté, on lui propose de créer sa page directement depuis la home
        $this->assertCount(1, $crawler->filter('form[name="creation-page-eleveur"]'));
    }

    public function testCreationPageEleveur()
    {
        $client = static::createClient();
        $user = UserUtils::create($client, $this);

        // tant qu'il n'a pas créé sa page eleveur il n'a pas le ROLE_ELEVEUR
        $this->assertFalse($user->hasRole(ERole::ELEVEUR));

        // on va sur la home en mode connecté, il y a le formulaire de création de page eleveur
        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('form[name="creation-page-eleveur"]')->form();
        $rand = rand();
        $nomElevage = 'Les Chartreux de Tatouine ' . $rand;
        $creationPageEleveurForm['creation-page-eleveur[nom]'] = $nomElevage;
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

    public function testCreationPageEleveurDeconnecte()
    {
        $client = static::createClient();

        $user = UserUtils::create($client, $this);
        $pageEleveurForm = $client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();

        $client->request('GET', '/logout');
        $pageEleveurForm['creation-page-eleveur[nom]'] = $this->getName() . rand();
        $client->submit($pageEleveurForm);

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testDeuxUserMemePage()
    {
        $client = static::createClient();
        $pageEleveur1 = UserUtils::createNewEleveur($client, $this);

        // connexion avec un nouvel user
        UserUtils::create($client, $this);

        //le 2eme user utilise le meme nom que pageEleveur1
        $pageEleveurForm = $client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();
        $pageEleveurForm['creation-page-eleveur[nom]'] = $pageEleveur1->getNom();
        $client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_CONFLICT, $client->getResponse()->getStatusCode());
        $this->assertEquals('Une page eleveur du meme nom existe deja', $client->getResponse()->getContent());
    }
}
