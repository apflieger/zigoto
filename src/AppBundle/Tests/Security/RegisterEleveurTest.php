<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 28/10/15
 * Time: 21:38
 */

namespace AppBundle\Tests\Security;


use AppBundle\Tests\UserUtils;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterEleveurTest extends WebTestCase
{

    public function testLoginForm()
    {
        $client = static::createClient();

        $user = UserUtils::create($client, $this);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/register/confirmed', $client->getResponse()->headers->get('Location'));

        // On est connecté sur ce nouvel utilisateur et on a le role ROLE_ELEVEUR
        $this->assertTrue($client->getContainer()->get('security.authorization_checker')->isGranted('ROLE_ELEVEUR'));

        UserUtils::delete($client, $user);
    }
}