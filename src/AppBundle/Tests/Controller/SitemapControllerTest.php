<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Repository\PageEleveurBranchRepository;
use AppBundle\Tests\TestUtils;
use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SitemapControllerTest extends WebTestCase
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

    public function test()
    {
        $peRepository = $this->getMockBuilder(PageEleveurBranchRepository::class)->disableOriginalConstructor()->getMock();
        /** @var EntityManager|PHPUnit_Framework_MockObject_MockObject $doctrine */
        $doctrine = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $doctrine->method('getRepository')->with('AppBundle:PageEleveurBranch')->willReturn($peRepository);
        $this->client->getContainer()->set('doctrine.orm.entity_manager', $doctrine);

        // simule les PE en bdd
        $pageEleveurBranch1 = new PageEleveurBranch();
        $pageEleveurBranch1->setSlug('test-sitemap1');

        $pageEleveurBranch2 = new PageEleveurBranch();
        $pageEleveurBranch2->setSlug('test-sitemap2');

        $peRepository->method('findAll')->willReturn([
            $pageEleveurBranch1,
            $pageEleveurBranch2
        ]);

        $crawler = $this->client->request('GET', '/sitemap.xml');
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertEquals('text/xml; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));

        /** @var Router $router */
        $router = $this->client->getContainer()->get('router');
        $this->assertContains(
            $router->generate('getPageEleveur_route', ['pageEleveurSlug' => 'test-sitemap1'], Router::ABSOLUTE_URL),
            $crawler->text());
        $this->assertContains(
            $router->generate('getPageEleveur_route', ['pageEleveurSlug' => 'test-sitemap2'], Router::ABSOLUTE_URL),
            $crawler->text());
    }
}