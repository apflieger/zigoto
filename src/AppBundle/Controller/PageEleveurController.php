<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 20:52
 */

namespace AppBundle\Controller;


use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Repository\PageEleveurRepository;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PageEleveurController extends Controller
{
    /**
     * @Route("/{pageEleveurSlug}", name="getPageEleveur")
     * @Method("GET")
     */
    public function getAction($pageEleveurSlug)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var PageEleveurRepository $pageEleveurRepository */
        $pageEleveurRepository = $entityManager->getRepository('AppBundle:PageEleveur');

        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $pageEleveurRepository->findBySlug($pageEleveurSlug);

        if (!$pageEleveur)
            throw $this->createNotFoundException();

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');

        /** @var AnonymousToken $token */
        $token = $tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();
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
            'commitId' => $pageEleveur->getCommit()->getId(),
            'nom' => $pageEleveur->getCommit()->getNom(),
            'description' => $pageEleveur->getCommit()->getDescription()
        ));
    }

    /**
     * @Route("/commit-page-eleveur", name="commitPageEleveur")
     * @Method("POST")
     * @param Request $request
     * @return Response
     * @throws HistoryException
     */
    public function commitAction(Request $request)
    {
        $jsonPageEleveur = json_decode($request->getContent());

        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');

        /** @var User $user */
        $user = $tokenStorage->getToken()->getUser();

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->container->get('zigoto.page_eleveur');

        try {
            $newCommit = $pageEleveurService->commit(
                $user,
                $jsonPageEleveur->id,
                $jsonPageEleveur->commitId,
                $jsonPageEleveur->nom,
                $jsonPageEleveur->description);
            return new Response($newCommit->getId());
        } catch (HistoryException $e) {
            switch ($e->getCode()) {
                case HistoryException::NON_FAST_FORWARD:
                    return new Response(
                        'Plusieurs éditions sont en cours, veuillez rafraichir la page.',
                        Response::HTTP_CONFLICT);
                    break;
                case HistoryException::DROIT_REFUSE:
                    return new Response(
                        'Vous ne pouvez pas modifier cette page. Vérifiez que vous êtes bien connecté.',
                        Response::HTTP_FORBIDDEN);
                    break;
                case HistoryException::BRANCHE_INCONNUE:
                    return new Response(
                        'Votre page a été supprimée.',
                        Response::HTTP_NOT_FOUND);
                    break;
            }
            throw $e;
        }
    }
}