<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\PageEleveur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageEleveurController extends Controller
{
    /**
     * @Route("/{eleveurURL}", name="pageEleveur")
     */
    public function pageEleveurAction($eleveurURL)
    {
        /**
         * @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
         */
        $doctrine = $this->container->get('doctrine');

        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $doctrine->getRepository('AppBundle:PageEleveur')->findOneBy(['url' => $eleveurURL]);

        if (!$pageEleveur)
            throw $this->createNotFoundException();

        return $this->render('page-eleveur.html.twig', ['pageEleveur' => $pageEleveur]);
    }
}