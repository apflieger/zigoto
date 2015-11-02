<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        /**
         * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
         */
        $authorizationChecker = $this->get('security.authorization_checker');

        if (!$authorizationChecker->isGranted('ROLE_ELEVEUR'))
            return $this->render('index.html.twig');
        else
            return new Response('Work in progress');

    }
}
