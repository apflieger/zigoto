<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurReflog;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreationPageEleveurController extends Controller
{
    /**
     * @Route("/creation-page-eleveur")
     * @Method("POST")
     */
    public function creationPageEleveurAction(Request $request)
    {
        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');

        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();
        $user->addRole(ERole::ELEVEUR);

        /**
         * @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
         */
        $doctrine = $this->container->get('doctrine');

        //Création de la page eleveur
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);
        $pageEleveur->setUrl($request->request->all()['elevage']['nom']);

        $doctrine->getManager()->persist($pageEleveur);

        //Ajout de la 1ere entrée dans le reflog de cette page
        $reflog = new PageEleveurReflog();
        $reflog->setUrl($pageEleveur->getUrl());
        $reflog->setDateTime(new \DateTime());
        $reflog->setLogEntry(0);
        $reflog->setPageEleveur($pageEleveur);
        $reflog->setUser($user);
        $reflog->setCommentaire("Création de la page");

        $doctrine->getManager()->persist($reflog);

        $doctrine->getManager()->flush();

        return $this->redirectToRoute('pageEleveur', ['eleveurURL' => $pageEleveur->getUrl()]);
    }
}