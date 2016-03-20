<?php

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\Contact;
use AppBundle\Tests\TestUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class ContactControllerTest extends WebTestCase
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

    public function testAnonyme()
    {
        $crawler = $this->client->request('GET', '/contact');

        $this->assertEquals('Zigotoo - nous contacter', $crawler->filter('title')->text());
        $this->assertEquals(1, $crawler->filter('form')->count());

        $this->assertEmpty($crawler->filter('form')->form()->get('form[email]')->getValue());
        $this->assertEmpty($crawler->filter('form')->form()->get('form[message]')->getValue());
    }

    public function testUserConnecte()
    {
        $this->testUtils->createUser();
        $crawler = $this->client->request('GET', '/contact');

        $this->assertEquals($this->testUtils->getUser()->getEmail(), $crawler->filter('form')->form()->get('form[email]')->getValue());
    }

    public function testEmailAsGetParam()
    {
        $this->testUtils->createUser();
        $crawler = $this->client->request('GET', '/contact?email=test@chmol.com');

        $this->assertEquals('test@chmol.com', $crawler->filter('form')->form()->get('form[email]')->getValue());
    }

    public function testSubmit()
    {
        $user = $this->testUtils->createUser()->getUser();

        /** @var EntityManager $entityManager */
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $contactRepository = $entityManager->getRepository('AppBundle:Contact');

        $this->assertEmpty($contactRepository->findBy(['email' => $user->getEmail()]));

        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->filter('form')->form();

        $form['form[message]'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at ornare lacus. ' .
        'Mauris semper lacus a metus malesuada, at malesuada elit condimentum. ' .
        'Proin euismod tellus vitae dolor vestibulum metus.';

        $this->client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $contactRepository->clear();

        /** @var Contact[] $contacts */
        $contacts = $contactRepository->findBy(['email' => $user->getEmail()]);
        $this->assertEquals(1, count($contacts));
        $this->assertStringStartsWith('Lorem', $contacts[0]->getMessage());
    }
}