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
use AppBundle\Entity\PageEleveurCommit;
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
         * @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
         */
        $doctrine = $this->container->get('doctrine');
        $pageEleveurRepository = $doctrine->getRepository('AppBundle:PageEleveur');

        $nomPageEleveur = $request->request->all()['elevage']['nom'];

        $urlPageEleveur = $this->convertToUrl($nomPageEleveur);

        if (count($pageEleveurRepository->findBy(['url' => $urlPageEleveur])) > 0)
            return new Response('Une page eleveur du meme nom existe deja', Response::HTTP_CONFLICT);

        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');
        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();

        if (count($pageEleveurRepository->findBy(['owner' => $user])) > 0)
            return new Response('Vous avez deja une page eleveur', Response::HTTP_CONFLICT);

        $user->addRole(ERole::ELEVEUR);

        //Création de la page eleveur
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);
        $pageEleveur->setUrl($urlPageEleveur);

        $pageEleveurCommit = new PageEleveurCommit();
        $pageEleveurCommit->setNom($nomPageEleveur);

        $pageEleveur->setCommit($pageEleveurCommit);

        //Ajout de la 1ere entrée dans le reflog de cette page
        $reflog = new PageEleveurReflog();
        $reflog->setUrl($pageEleveur->getUrl());
        $reflog->setDateTime(new \DateTime());
        $reflog->setLogEntry(0);
        $reflog->setPageEleveur($pageEleveur);
        $reflog->setUser($user);
        $reflog->setCommentaire("Création de la page");

        $doctrine->getManager()->persist($pageEleveurCommit);
        $doctrine->getManager()->persist($pageEleveur);
        $doctrine->getManager()->persist($reflog);

        $doctrine->getManager()->flush();

        return $this->redirectToRoute('pageEleveur', ['eleveurURL' => $pageEleveur->getUrl()]);
    }

    /**
     * @param $str string
     * @return string
     */
    public function convertToUrl($str)
    {
        // conversion de tous les caractères spéciaux vers de l'ascii
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $str);

        // suppression de tous les caractères qui ne sont pas des chiffres, lettres, ou "_+- "
        $ascii = preg_replace("/[^a-zA-Z0-9\/_+ -]/", '', $ascii);

        // lowercase
        $ascii = strtolower($ascii);

        // remplacement de tout ce qui n'est pas chiffres ou lettres par le séparateur '-'
        $ascii = preg_replace("/[\/_+ -]+/", '-', $ascii);

        // trim
        return trim($ascii, '-');
    }
}