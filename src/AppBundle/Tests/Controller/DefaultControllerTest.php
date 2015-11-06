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


    public function testIndexEleveur()
    {
        $client = static::createClient();
        $user = UserUtils::create($client, $this);

        $this->assertFalse($user->hasRole(ERole::ELEVEUR));
        $crawler = $client->request('GET', '/');
        $creationPageEleveurForm = $crawler->filter('#creation-page-eleveur')->form();
        $nomElevage = "testNouvelEleveur" . rand();
        $creationPageEleveurForm['elevage[nom]'] = $nomElevage;
        $client->submit($creationPageEleveurForm);

        // Redirection vers la home de l'éleveur
        $this->assertEquals('Bonjour '.$user->getUsername(), $client->request('GET', '/')->filter('h1')->text());
    }
}
