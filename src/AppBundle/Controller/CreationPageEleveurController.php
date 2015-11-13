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
use AppBundle\Service\PageEleveurException;
use AppBundle\Service\PageEleveurService;
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
        // On récupère le paramètre POST venant du formulaire de création de page eleveur
        $nomPageEleveur = $request->request->all()['elevage']['nom'];

        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');
        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();

        /**
         * @var $pageEleveurService PageEleveurService
         */
        $pageEleveurService = $this->container->get('page_eleveur');

        try
        {
            $url = $pageEleveurService->create($nomPageEleveur, $user);
        } catch (PageEleveurException $e)
        {
            return new Response($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->redirectToRoute('pageEleveur', ['eleveurURL' => $url]);
    }
}