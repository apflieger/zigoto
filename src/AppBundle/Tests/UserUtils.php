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
     */
    public static function create(Client $client, WebTestCase $test)
    {
        $registrationForm = $client->request('GET', '/register/')->filter('form')->form();

        // Création d'un nouvel utilisateur
        $registrationForm['fos_user_registration_form[username]'] = $test->getName();
        $registrationForm['fos_user_registration_form[email]'] = $test->getName() . '@gizoto.com';
        $registrationForm['fos_user_registration_form[plainPassword][first]'] = 'test';
        $registrationForm['fos_user_registration_form[plainPassword][second]'] = 'test';

        $client->submit($registrationForm);
        return $client->getContainer()->get('security.context')->getToken()->getUser();
    }

    public static function delete(Client $client, User $user){
        /**
         * @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
         */
        $doctrine = $client->getContainer()->get('doctrine');

        $doctrine->getManager()->remove($user);
        $doctrine->getManager()->flush();
    }

}