<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Controller\PageEleveurController;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PageEleveurControllerTest extends WebTestCase
{

    public function test404()
    {
        $client = static::createClient();

        $client->request('GET', '/nonexisting-eleveur');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testContent()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        /**
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $client->getContainer()->get('page_eleveur');

        $commit = new PageEleveurCommit($pageEleveur->getNom(), 'nouvelle description', $pageEleveur->getCommit());
        $pageEleveurService->commit($pageEleveur->getId(), $commit, $pageEleveur->getOwner());

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());

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
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        // Modification du nom et de la description de la page
        $client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            json_encode(array(
                'id' => $pageEleveur->getId(),
                'commit' => array(
                    'id' => $pageEleveur->getCommit()->getId(),
                    'nom' => 'nouveau nom',
                    'description' => 'description non nulle'
                )
            )));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // La réponse du POST retourne l'identifiant du commit créé dans le contenu
        $client->getResponse()->getContent();

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());
        $this->assertEquals('nouveau nom', $crawler->filter('title')->text());
        $this->assertEquals('description non nulle', $crawler->filter('#description')->text());
    }

    public function testDroitCommitRefuse()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        // Connexion avec un autre user
        UserUtils::create($client, $this);

        $client->request('POST', '/commit-page-eleveur',
            array(), array(), array(),
            PageEleveurController::jsonPageEleveur($pageEleveur));

        $this->assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function testAccesOwner()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());

        $this->assertContains('owner', $crawler->html());
    }

    public function testAccesAnonyme()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        UserUtils::logout($client);

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());

        $this->assertNotContains('owner', $crawler->html());
    }
}