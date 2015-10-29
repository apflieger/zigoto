<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 28/10/15
 * Time: 21:38
 */

namespace AppBundle\Tests\Security;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterEleveurTest extends WebTestCase
{

    public function testLoginForm()
    {
        $client = static::createClient();

        $registrationForm = $client->request('GET', '/register/')->filter('form')->form();

        // Création d'un nouvel utilisateur
        $registrationForm['fos_user_registration_form[username]'] = 'test';
        $registrationForm['fos_user_registration_form[email]'] = 'test@gizoto.com';
        $registrationForm['fos_user_registration_form[plainPassword][first]'] = 'test';
        $registrationForm['fos_user_registration_form[plainPassword][second]'] = 'test';

        // On doit arriver sur la page de confirmation
        $confirmation = $client->submit($registrationForm);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/register/confirmed', $client->getResponse()->headers->get('Location'));

        // On est connecté sur ce nouvel utilisateur et on a le role ROLE_ELEVEUR
        $this->assertTrue($client->getContainer()->get('security.authorization_checker')->isGranted('ROLE_ELEVEUR'));
    }
}