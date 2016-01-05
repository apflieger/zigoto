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
use AppBundle\Repository\PageEleveurRepository;
use AppBundle\Service\HistoryException;
use AppBundle\Service\HistoryService;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var PageEleveurRepository $pageEleveurRepository */
        $pageEleveurRepository = $entityManager->getRepository('AppBundle:PageEleveur');

        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $pageEleveurRepository->findByUrl($eleveurURL);

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
     */
    public function commitAction(Request $request)
    {
        $clientPageEleveur = json_decode($request->getContent());

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        /** @var User $user */
        $user = $tokenStorage->getToken()->getUser();

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $pageEleveurRepository = $entityManager->getRepository('AppBundle:PageEleveur');

        $pageEleveur = $pageEleveurRepository->find($clientPageEleveur->id);

        if ($pageEleveur->getOwner()->getId() !== $user->getId())
            return new Response('Vous n\'avez pas les droit de modifier la page', Response::HTTP_FORBIDDEN);

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var EntityRepository $pageEleveurCommitRepository */
        $pageEleveurCommitRepository = $entityManager->getRepository('AppBundle:PageEleveurCommit');
        $commit = $pageEleveurCommitRepository->find($clientPageEleveur->commit->id);

        $nom = $clientPageEleveur->commit->nom;

        $description = $clientPageEleveur->commit->description;

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->container->get('zigoto.page_eleveur');

        try {
            $newCommit = $pageEleveurService->commit($user, $clientPageEleveur->id, $commit->getId(), $nom, $description);
            return new Response($newCommit->getId());
        } catch (HistoryException $e) {
            $message = '';
            switch ($e->getType()) {
                case HistoryException::BRANCHE_INCONNUE:
                    $message = 'Votre page a été supprimée.';
                    break;
                case HistoryException::NON_FAST_FORWARD:
                    $message = 'Plusieurs édition de la page sont en cours, veuillez rafraichir.';
                    break;
            }
            return new Response($message, Response::HTTP_CONFLICT);
        }
    }
}