<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:56
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Service\PageEleveurService;
use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
        $script = $crawler->filter('script')->reduce(function ($script) {
            return strpos($script->text(), 'Initialisation des constantes Javascript');
        });
        $this->assertEquals(1, $script->count());

        $this->assertContains('"id": "'.$pageEleveur->getId().'"', $script->text());
        $this->assertContains('"commit": "'.$commit->getId().'"', $script->text());
        $this->assertContains('"description": "nouvelle description"', $script->text());
    }

    public function testCommit()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        // Modification du nom et de la description de la page
        $client->request('POST', '/commit-page-eleveur',
            ['pageEleveur.head' => $pageEleveur->getCommit()->getId(),
                'pageEleveur.id' => $pageEleveur->getId(),
                'pageEleveur.nom' => 'nouveau nom',
                'pageEleveur.description' => 'description non nulle']);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // La réponse du POST retourne l'identifiant du commit créé dans le contenu
        $newCommitId = $client->getResponse()->getContent();

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());
        $this->assertEquals('nouveau nom', $crawler->filter('title')->text());
        $this->assertEquals('description non nulle', $crawler->filter('#description')->text());


        // Commit sans modifier le contenu de la page. Les paramètres sont optionnels pour
        // pouvoir envoyer seulement ce qu'on veut modifier
        $client->request('POST', '/commit-page-eleveur',
            ['pageEleveur.head' => $newCommitId,
                'pageEleveur.id' => $pageEleveur->getId()]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // La page n'a pas changé
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
            ['pageEleveur.head' => $pageEleveur->getCommit()->getId(),
                'pageEleveur.id' => $pageEleveur->getId(),
                'pageEleveur.description' => $this->getName()]);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }
}
