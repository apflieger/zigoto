<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DefaultControllerTest extends WebTestCase
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

    public function testIndex_Anonyme()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créez votre page éleveur', $crawler->filter('h1')->text());
        $this->assertEquals(1, $crawler->filter('a[href="/login"]')->count());
        $this->assertEquals(1, $crawler->filter('a[href="/register"]')->count());
    }

    public function testIndex_User()
    {
        $this->testUtils->createUser();

        $crawler = $this->client->request('GET', '/');

        // Quand l'utilisateur est connecté, on lui propose de créer sa page directement depuis la home
        $this->assertCount(1, $crawler->filter('form[name="creation-page-eleveur"]'));
        $this->assertEquals(1, $crawler->filter('a[href="/logout"]')->count());
    }

    public function testIndex_Eleveur()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/');

        // L'eleveur a un lien vers sa page
        $this->assertEquals(1, $crawler->filter('a[href="/' . $pageEleveur->getSlug() . '"]')->count());
        $this->assertEquals(1, $crawler->filter('a[href="/logout"]')->count());
    }

    public function testCreationPageEleveur_Success()
    {
        $user = $this->testUtils->createUser()->getUser();

        // on va sur la home en mode connecté, il y a le formulaire de création de page eleveur
        $crawler = $this->client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('form[name="creation-page-eleveur"]')->form();
        $rand = rand();
        $nomElevage = 'Les Chartreux de Tatouine ' . $rand;
        $creationPageEleveurForm['creation-page-eleveur[nom]'] = $nomElevage;
        $this->client->submit($creationPageEleveurForm);

        // Redirection vers sa page eleveur fraichement créé
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertEquals('/les-chartreux-de-tatouine-' . $rand, $this->client->getRequest()->getRequestUri());
        $this->assertEquals('Bonjour '.$user->getUsername(), $this->client->request('GET', '/')->filter('h1')->text());
    }

    public function testCreationPageEleveur_Deconnecte()
    {
        $this->testUtils->createUser();
        $pageEleveurForm = $this->client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();

        $this->testUtils->logout();

        $pageEleveurForm['creation-page-eleveur[nom]'] = $this->getName() . rand();
        $this->client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testCreationPageEleveur_DeuxUserMemePage()
    {
        $pageEleveur1 = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // connexion avec un nouvel user
        $this->testUtils->createUser();

        //le 2eme user utilise le meme nom que pageEleveur1
        $pageEleveurForm = $this->client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();
        $pageEleveurForm['creation-page-eleveur[nom]'] = $pageEleveur1->getNom();
        $this->client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('Une page éleveur du même nom existe déjà.', $this->client->getResponse()->getContent());
    }

    public function testCreationPageEleveur_NomInvalide()
    {
        // connexion avec un nouvel user
        $this->testUtils->createUser();

        $pageEleveurForm = $this->client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();
        $pageEleveurForm['creation-page-eleveur[nom]'] = '--';
        $this->client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_NOT_ACCEPTABLE, $this->client->getResponse()->getStatusCode());
    }
}
