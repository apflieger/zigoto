<?php

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\Contact;
use AppBundle\Tests\TestUtils;
use Doctrine\ORM\EntityManager;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\HttpFoundation\Response;

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

        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at ornare lacus. ' .
        'Mauris semper lacus a metus malesuada, at malesuada elit condimentum. ' .
        'Proin euismod tellus vitae dolor vestibulum metus.';

        $form['form[message]'] = $message;

        $this->client->enableProfiler(); // permet de profiler les mails qui vont être envoyés
        $this->client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $contactRepository->clear(); // permet de s'assurer que les 'find' vont bien chercher en bdd

        // On vérifie que le contact est en bdd
        /** @var Contact[] $contacts */
        $contacts = $contactRepository->findBy(['email' => $user->getEmail()]);
        $this->assertEquals(1, count($contacts));
        $this->assertEquals($message, $contacts[0]->getMessage());
        $this->assertEquals($user->getId(), $contacts[0]->getUser()->getId());

        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');

        // 2 mails ont du être envoyés
        $this->assertEquals(2, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();

        // Le 1er est l'accusé réception à l'utilisateur
        /** @var Swift_Message $accuseReception */
        $accuseReception = $collectedMessages[0];

        $this->assertEquals('Formulaire de contact', $accuseReception->getSubject());
        $this->assertEquals('no-reply@zigotoo.com', key($accuseReception->getFrom()));
        $this->assertEquals('Zigotoo', current($accuseReception->getFrom()));
        $this->assertEquals($user->getEmail(), key($accuseReception->getTo()));
        $this->assertContains($message, $accuseReception->getBody(), 'L\'accusé de réception ne contient pas le message');

        // Le 2eme est une notififaction aux admins
        /** @var Swift_Message $mailAdmins */
        $mailAdmins = $collectedMessages[1];

        $this->assertEquals('Reception formulaire de contact', $mailAdmins->getSubject());
        $this->assertEquals('app@zigotoo.com', key($mailAdmins->getFrom()));
        $this->assertEquals(['pflieger.arnaud@gmail.com', 'MehdiBelkacemi@gmail.com'], array_keys($mailAdmins->getTo()));
        $this->assertContains($message, $mailAdmins->getBody());
        $this->assertContains($user->getEmail(), $mailAdmins->getBody());
    }

    public function testConfirmation()
    {
        $user = $this->testUtils->createUser()->getUser();

        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->filter('form')->form();

        $form['form[message]'] = 'test';
        $this->client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $confirmationCrawler = $this->client->followRedirect();

        $this->assertContains('Nous vous répondrons à l\'adresse ' . $user->getEmail(),
            $confirmationCrawler->filter('#confirmation')->text());
    }

    public function testValidationEmail()
    {
        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->filter('form')->form();

        // L'email est vide
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isClientError());
    }

    public function testConfirmationSansFlashBag()
    {
        $this->client->request('GET', '/contact/confirmation');

        $this->assertTrue($this->client->getResponse()->isRedirect('/contact'));
    }
}