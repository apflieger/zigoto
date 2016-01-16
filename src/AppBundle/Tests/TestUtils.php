<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 22:48
 */

namespace AppBundle\Tests;


use AppBundle\Entity\PageEleveur;
use AppBundle\Service\PageEleveurService;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestUtils
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var WebTestCase
     */
    private $test;

    /**
     * @var User
     */
    private $user;

    /**
     * @var PageEleveur
     */
    private $pageEleveur;

    public function __construct(Client $client, WebTestCase $test)
    {
        $this->client = $client;
        $this->test = $test;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return PageEleveur
     */
    public function getPageEleveur()
    {
        return $this->pageEleveur;
    }

    /**
     * Créé et authentifie un nouveau user
     * @return $this
     */
    public function createUser()
    {
        /** @var $userManager UserManager */
        $userManager = $this->client->getContainer()->get('fos_user.user_manager');

        // Création d'un utilisateur
        $user = $userManager->createUser();

        // le random permet les exécutions successives des mêmes tests
        $username = $this->test->getName() . rand();
        $user->setUsername($username);
        $user->setEmail($username . '@gizoto.com');
        $user->setPlainPassword('test');
        $user->setEnabled(true);
        $userManager->updateUser($user);

        // En environnement test, on utilise l'authentification http plutot que par session, c'est plus performant
        $this->client->setServerParameter('PHP_AUTH_USER', $username);
        $this->client->setServerParameter('PHP_AUTH_PW', 'test');

        $this->user = $user;

        return $this;
    }

    public function logout()
    {
        $this->client->setServerParameters(array());
        $this->client->getCookieJar()->clear();
        $this->user = null;
        $this->pageEleveur = null;
    }

    /**
     * Créé une page eleveur à l'utilisateur connecté
     * @return $this
     * @throws \AppBundle\Controller\DisplayableException
     */
    public function toEleveur()
    {
        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigoto.page_eleveur');

        // l'elevage aussi contiendra le random de par le username
        $this->pageEleveur = $pageEleveurService->create('elevage_' . $this->user->getUsername(), $this->user);

        return $this;
    }

    public function addAnimal()
    {
        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigoto.page_eleveur');
        $pageEleveurService->addAnimal(
            $this->user,
            $this->pageEleveur->getId(),
            $this->pageEleveur->getCommit()->getId()
        );

        return $this;
    }
}