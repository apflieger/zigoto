<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\PageEleveurController;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\TestUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PageEleveurControllerTest extends WebTestCase
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

    public function test404()
    {
        $this->client->request('GET', '/nonexisting-eleveur');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testContent()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigoto.page_eleveur');

        $pageEleveurService->commit(
            $pageEleveur->getOwner(),
            $pageEleveur->getId(),
            $pageEleveur->getCommit()->getId(),
            $pageEleveur->getNom(),
            'nouvelle description'
            );

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('title')->text());
        $this->assertEquals('nouvelle description', $crawler->filter('#description')->text());

        // On vérifie qu'il y a un script qui passe l'id du commit au JS
        $script = $crawler->filter('script')->reduce(function (Crawler $script) {
            return strpos($script->text(), 'const-js');
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains(PageEleveurController::jsonPageEleveur($pageEleveur), $script->text());
    }

    public function testCommit()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // Modification du nom et de la description de la page
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            json_encode(array(
                'id' => $pageEleveur->getId(),
                'commitId' => $pageEleveur->getCommit()->getId(),
                'nom' => 'nouveau nom',
                'description' => 'description non nulle'
            )));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // La réponse du POST retourne l'identifiant du commit créé dans le contenu
        $this->assertEquals(PageEleveurController::jsonPageEleveur($pageEleveur), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());
        $this->assertEquals('nouveau nom', $crawler->filter('title')->text());
        $this->assertEquals('description non nulle', $crawler->filter('#description')->text());
    }

    public function testDroitCommitRefuse()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        // Connexion avec un autre user
        $this->testUtils->createUser();

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            PageEleveurController::jsonPageEleveur($pageEleveur));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testAccesOwner()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertContains('owner', $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->testUtils->logout();

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertNotContains('owner', $crawler->html(), 'ca marche pas !');
    }

    public function testCommitBrancheInconnue()
    {
        $this->testUtils->createUser();

        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            json_encode(array(
                'id' => -1,
                'commitId' => -1,
                'nom' => '',
                'description' => ''
            )));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testCommitNonFastForward()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $parentCommitId = $pageEleveur->getCommit()->getId();

        // 1er commit
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            json_encode(array(
                'id' => $pageEleveur->getId(),
                'commitId' => $parentCommitId,
                'nom' => '',
                'description' => ''
            )));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // 2eme commit avec le meme parent que le 1er commit
        $this->client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            json_encode(array(
                'id' => $pageEleveur->getId(),
                'commitId' => $parentCommitId,
                'nom' => '',
                'description' => ''
            )));

        $this->assertEquals(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testAddAnimal()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->getPageEleveur();

        $this->client->request('POST', '/add-animal',
            array(), array(), array(),
            json_encode(array(
                'id' => $pageEleveur->getId(),
                'commitId' => $pageEleveur->getCommit()->getId()
            ))
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/animal/' . $pageEleveur->getCommit()->getAnimaux()[0]->getId());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAnimal_thumbnail()
    {
        $pageEleveur = $this->testUtils->createUser()->toEleveur()->addAnimal()->getPageEleveur();
        $animal = $pageEleveur->getCommit()->getAnimaux()[0];

        $crawler = $this->client->request('GET', '/' . $pageEleveur->getSlug());

        $this->assertEquals($animal->getNom(), $crawler->filter('.animaux ul > li')->text());
    }
}