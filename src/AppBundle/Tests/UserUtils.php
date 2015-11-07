<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 22:48
 */

namespace AppBundle\Tests;


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
        $registrationForm = $client->request('GET', '/register/')->filter('form')->form();

        $username = $test->getName() . rand();
        // Création d'un nouvel utilisateur
        $registrationForm['fos_user_registration_form[username]'] = $username;
        $registrationForm['fos_user_registration_form[email]'] = $username . '@gizoto.com';
        $registrationForm['fos_user_registration_form[plainPassword][first]'] = 'test';
        $registrationForm['fos_user_registration_form[plainPassword][second]'] = 'test';

        $client->submit($registrationForm);
        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $client->getContainer()->get('security.token_storage');

        $user = $tokenStorage->getToken()->getUser();

        if ($user === 'anon.')
            throw new \Exception("Creation du user a échouée : " . $test->getName());
        return $user;
    }
}