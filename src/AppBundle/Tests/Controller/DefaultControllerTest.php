<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $this->assertCount(1, $crawler->filter('form#creation-page-eleveur'));
    }

}
