<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\PageEleveur;
use AppBundle\Service\PageEleveurService;
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
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $this->container->get('page_eleveur');

        $pageEleveur = $pageEleveurService->getForUrl($eleveurURL);

        if (!$pageEleveur)
            throw $this->createNotFoundException();

        return $this->render('page-eleveur.html.twig', ['pageEleveur' => $pageEleveur]);
    }
}