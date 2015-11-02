<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        if (!$authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->render('index.html.twig');


    }
}
