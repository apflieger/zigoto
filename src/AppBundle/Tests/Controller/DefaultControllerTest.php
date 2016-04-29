<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

    public function testGetTeaser()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals('Créez votre site d\'éleveur', $crawler->filter('h1')->text());
        $this->assertEquals('Zigotoo - Créez votre site d\'éleveur', $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('meta[name="description"]')->count());
        $this->assertEquals(1, $crawler->filter('nav.global-header a[href="/"]')->count());

        // pas de formulaire de creation page eleveur
        $this->assertEquals(0, $crawler->filter('form')->count());
    }

    public function testLinkQuiSommesNous()
    {
        $crawler = $this->client->request('GET', '/');

        $this->client->click($crawler->filter('footer a[href="/qui-sommes-nous"]')->link());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testLinkContact()
    {
        $crawler = $this->client->request('GET', '/');

        $this->client->click($crawler->filter('footer a[href="/contact"]')->link());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testCreationPageEleveur()
    {
        $user = $this->testUtils->createUser()->getUser();

        $crawler = $this->client->request('GET', '/');


        $this->assertEquals('Zigotoo - Créez votre page eleveur', $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('a[href="/logout"]')->count());

        // Quand l'utilisateur est connecté, on lui propose de créer sa page directement depuis la home
        $this->assertCount(1, $crawler->filter('form[name="creation-page-eleveur"]'));
    }

    public function testCreationPageEleveur_Success()
    {
        // Création de compte
        $user = $this->testUtils->createUser()->getUser();

        // Formulaire de création de page eleveur
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals('Nom de l\'élevage', $crawler->filter('form[name="creation-page-eleveur"] label')->text());
        $this->assertEquals('Créer ma page éleveur', $crawler->filter('form[name="creation-page-eleveur"] [type="submit"]')->text());

        $creationPageEleveurForm = $crawler->filter('form[name="creation-page-eleveur"]')->form();
        $rand = rand();
        $nomElevage = 'Les Chartreux de Tatouine ' . $rand;
        $creationPageEleveurForm['creation-page-eleveur[nom]'] = $nomElevage;
        $this->client->submit($creationPageEleveurForm);

        // Redirection vers sa page eleveur fraichement créé
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertEquals('/les-chartreux-de-tatouine-' . $rand, $this->client->getRequest()->getRequestUri());
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


    public function testCreationPageEleveur_UnUserDeuxPages()
    {
        //Ce cas n'est pas sensé se produire. On log ca en notice
        $this->testUtils->createUser();

        //Affichage de la home avec le formulaire de creation de page eleveur
        $pageEleveurForm = $this->client->request('GET', '/')->filter('form[name="creation-page-eleveur"]')->form();

        //Creation d'une page eleveur
        $this->testUtils->toEleveur();


        $pageEleveurForm['creation-page-eleveur[nom]'] = '2eme page eleveur';
        $this->client->submit($pageEleveurForm);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('Vous avez déjà une page éleveur.', $this->client->getResponse()->getContent());
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

    public function testHomeEleveur()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('a[href="'.'/' . $pageEleveur->getSlug().'"]')->count());
    }
}
