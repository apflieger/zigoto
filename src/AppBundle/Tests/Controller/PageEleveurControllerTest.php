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

        $pageEleveurService->commit($pageEleveur->getUrl(),
            new PageEleveurCommit($pageEleveur->getNom(), 'nouvelle description', $pageEleveur->getCommit()),
            $pageEleveur->getOwner());

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());

        $this->assertEquals($pageEleveur->getNom(), $crawler->filter('h1')->text());
        $this->assertEquals('nouvelle description', $crawler->filter('#description')->text());
    }

    public function testCommit()
    {
        $client = static::createClient();
        $pageEleveur = UserUtils::createNewEleveur($client, $this);

        $client->request('POST', '/' . $pageEleveur->getUrl(),
            ['head' => $pageEleveur->getCommit()->getId(),
                'description' => $this->getName()]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/' . $pageEleveur->getUrl());
        $this->assertEquals($this->getName(), $crawler->filter('#description')->text());
    }
}
