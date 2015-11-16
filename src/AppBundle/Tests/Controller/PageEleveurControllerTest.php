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
        $this->assertEquals(1, $crawler->filter('script')->reduce(function($script) use ($commit){
            return strpos($script->text(), 'var headCommit="'.$commit->getId().'";');
        })->count());
    }

    public function testCommit()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        $client->request('POST', '/commit-page-eleveur',
            ['head' => $pageEleveur->getCommit()->getId(),
                'pageEleveur' => $pageEleveur->getId(),
                'description' => $this->getName()]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());
        $this->assertEquals($this->getName(), $crawler->filter('#description')->text());
    }
}
