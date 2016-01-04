<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 22:48
 */

namespace AppBundle\Tests;


use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Service\PageEleveurService;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserUtils
{
    /**
     * @param Client $client
     * @param WebTestCase $test
     * @return User
     * @throws \Exception
     */
    public static function create(Client $client, WebTestCase $test)
    {
        /**
         * @var $userManager UserManager
         */
        $userManager = $client->getContainer()->get('fos_user.user_manager');
        $username = $test->getName() . rand();
        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($username . '@gizoto.com');
        $user->setPlainPassword('test');
        $user->setEnabled(true);
        $userManager->updateUser($user);

        $client->setServerParameter('PHP_AUTH_USER', $username);
        $client->setServerParameter('PHP_AUTH_PW', 'test');
        return $user;
    }

    public static function createNewEleveur(Client $client, WebTestCase $test)
    {
        $user = self::create($client, $test);

        /**
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $client->getContainer()->get('page_eleveur');
        return $pageEleveurService->create(new PageEleveur(new PageEleveurCommit('elevage_' . $user->getUsername(), '', null), $user), $user);
    }

    public static function logout(Client $client)
    {
        $client->setServerParameters(array());
        $client->getCookieJar()->clear();
    }
}