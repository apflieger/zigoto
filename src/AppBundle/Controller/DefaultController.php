<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');

        $user = $tokenStorage->getToken()->getUser();
        if ($user == 'anon.' || !$user->hasRole('ROLE_ELEVEUR'))
            return $this->render('index.html.twig');
        else
            return new Response('Work in progress');

    }
}
