<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Entity\User;
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
        if ($user == 'anon.' || !$user->hasRole(ERole::ELEVEUR))
            return $this->render('index.html.twig');
        else
            return $this->render('index-eleveur.html.twig', ['username' => $user->getUserName()]);

    }
}
