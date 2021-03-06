<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 22:48
 */

namespace AppBundle\Tests;


use AppBundle\Entity\User;
use AppBundle\Entity\PageEleveur;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageAnimalService;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Doctrine\UserManager;
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
        /** @var User $user */
        $user = $userManager->createUser();

        // le random permet les exécutions successives des mêmes tests
        $username = $this->test->getName() . rand();
        $user->setUsername($username);
        $user->setEmail($username . '@zigotoo.com');
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

    public function clearEntities()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
    }

    /**
     * Créé une page eleveur à l'utilisateur connecté
     * @return $this
     * @throws HistoryException
     */
    public function toEleveur()
    {
        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        // l'elevage aussi contiendra le random de par le username
        $this->pageEleveur = $pageEleveurService->create('elevage_' . $this->user->getUsername(), $this->user);

        return $this;
    }

    public function addAnimal()
    {
        /** @var PageAnimalService $pageAnimalService */
        $pageAnimalService = $this->client->getContainer()->get('zigotoo.page_animal');

        if (count($this->pageEleveur->getAnimaux()) === 0)
            $this->pageEleveur->setAnimaux([]);

        $animaux = $this->pageEleveur->getAnimaux();
        array_push($animaux, $pageAnimalService->create($this->user));
        $this->pageEleveur->setAnimaux($animaux);

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->client->getContainer()->get('zigotoo.page_eleveur');

        $this->pageEleveur = $pageEleveurService->commit(
            $this->user,
            $this->pageEleveur
        );

        return $this;
    }
}