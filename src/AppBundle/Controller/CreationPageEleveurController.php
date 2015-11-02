<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CreationPageEleveurController extends Controller
{
    /**
     * @Route("/creation-page-eleveur")
     * @Method("POST")
     */
    public function creationPageEleveurAction()
    {
        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');

        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();
        $user->addRole('ROLE_ELEVEUR');

        /**
         * @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
         */
        $doctrine = $this->container->get('doctrine');

        $doctrine->getManager()->flush();
        return new Response("woot");
    }
}