<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Service\PageEleveurService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PageEleveurController extends Controller
{
    /**
     * @Route("/{eleveurURL}", name="getPageEleveur")
     * @Method("GET")
     */
    public function getAction($eleveurURL)
    {
        /**
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $this->container->get('page_eleveur');

        $pageEleveur = $pageEleveurService->getByUrl($eleveurURL);

        if (!$pageEleveur)
            throw $this->createNotFoundException();

        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');
        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();
        $isOwner = $user !== 'anon.' && $pageEleveur->getOwner()->getId() === $user->getId();

        return $this->render('page-eleveur.html.twig', array(
            'pageEleveur' => $pageEleveur,
            'jsonPageEleveur' => self::jsonPageEleveur($pageEleveur),
            'isOwner' => $isOwner));
    }

    /**
     * @param PageEleveur $pageEleveur
     * @return string
     */
    public static function jsonPageEleveur(PageEleveur $pageEleveur)
    {
        return json_encode(array(
            'id' => $pageEleveur->getId(),
            'commit' => array(
                'id' => $pageEleveur->getCommit()->getId(),
                'nom' => $pageEleveur->getCommit()->getNom(),
                'description' => $pageEleveur->getCommit()->getDescription()
            )
        ));
    }

    /**
     * @Route("/commit-page-eleveur", name="commitPageEleveur")
     * @Method("POST")
     * @param Request $request
     * @return Response
     * @throws \AppBundle\Service\PageEleveurException
     */
    public function commitAction(Request $request)
    {
        $clientPageEleveur = json_decode($request->getContent());

        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');
        /**
         * @var User $user
         */
        $user = $tokenStorage->getToken()->getUser();

        /**
         * @var PageEleveurService $pageEleveurService
         */
        $pageEleveurService = $this->container->get('page_eleveur');

        $pageEleveur = $pageEleveurService->get($clientPageEleveur->id);

        if ($pageEleveur->getOwner()->getId() !== $user->getId())
            return new Response('Vous n\'avez pas les droit de modifier la page', Response::HTTP_FORBIDDEN);

        $commit = $pageEleveurService->getCommit($clientPageEleveur->commit->id);

        $nom = $clientPageEleveur->commit->nom;

        $description = $clientPageEleveur->commit->description;

        $newCommit = new PageEleveurCommit($nom, $description, $commit);

        $pageEleveurService->commit($clientPageEleveur->id, $newCommit, $user);

        return new Response($newCommit->getId());
    }
}