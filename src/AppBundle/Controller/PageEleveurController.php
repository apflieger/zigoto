<?php

namespace AppBundle\Controller;

use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageAnimalService;
use AppBundle\Service\PageEleveurService;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route(service="zigotoo.page_eleveur_controller")
 */
class PageEleveurController
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /** @var TwigEngine */
    private $templating;

    /** @var Serializer */
    private $serializer;

    /** @var PageEleveurService */
    private $pageEleveurService;

    /** @var PageAnimalService */
    private $pageAnimalService;

    /** @var Logger */
    private $logger;

    /**
     * PageEleveurController constructor.
     * @param TokenStorage $tokenStorage
     * @param RouterInterface $router
     * @param TwigEngine $templating
     * @param Serializer $serializer
     * @param PageEleveurService $pageEleveurService
     * @param PageAnimalService $pageAnimalService
     * @param Logger $logger
     */
    public function __construct(
        TokenStorage $tokenStorage,
        RouterInterface $router,
        TwigEngine $templating,
        Serializer $serializer,
        PageEleveurService $pageEleveurService,
        PageAnimalService $pageAnimalService,
        Logger $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->templating = $templating;
        $this->serializer = $serializer;
        $this->pageEleveurService = $pageEleveurService;
        $this->pageAnimalService = $pageAnimalService;
        $this->logger = $logger;
    }

    /**
     * @Route("/elevage/{pageEleveurSlug}", name="getPageEleveur_route")
     * @Method("GET")
     *
     * @param $pageEleveurSlug
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getAction($pageEleveurSlug, Request $request)
    {
        $pageEleveur = $this->pageEleveurService->findBySlug($pageEleveurSlug);

        if (!$pageEleveur)
            throw new NotFoundHttpException(null, null);

        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();
        $isOwner = $user !== 'anon.' && $pageEleveur->getOwner()->getId() === $user->getId();
        $isPreview = $request->query->has('preview');

        if ($isPreview && !$isOwner) {
            $this->logger->notice('Requete visiteur sur page eleveur en mode preview', [
                    'user' => $user,
                    'pageEleveurSlug' => $pageEleveurSlug,
                    'pageEleveurId' => $pageEleveur->getId()
            ]);
            return new RedirectResponse(
                $this->router->generate('getPageEleveur_route', ['pageEleveurSlug' => $pageEleveurSlug])
            );
        }

        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'editable/page-eleveur',
            'pageEleveur' => $pageEleveur,
            'isEditable' => $isOwner && !$isPreview
        ]);
    }

    /**
     * @param PageEleveur $pageEleveur
     * @return string
     */
    private function jsonPageEleveur(PageEleveur $pageEleveur)
    {
        return $this->serializer->serialize($pageEleveur, 'json');
    }

    /**
     * @Route("/commit-page-eleveur", name="commitPageEleveur_route")
     * @Method("POST")
     * @param Request $request
     * @return Response
     * @throws HistoryException
     */
    public function commitAction(Request $request)
    {
        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $this->serializer->deserialize($request->getContent(), PageEleveur::class, 'json');

        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if ($user === 'anon.') {
            return new RedirectResponse($this->router->generate('fos_user_security_login'));
        }

        try {
            $pageEleveur = $this->pageEleveurService->commit($user, $pageEleveur);
            return new Response(self::jsonPageEleveur($pageEleveur));
        } catch (HistoryException $e) {
            return $this->createErrorResponse($e, $user, $pageEleveur);
        }
    }

    /**
     * @Route("/add-animal", name="addAnimal_route")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function addAnimalAction(Request $request)
    {
        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $this->serializer->deserialize($request->getContent(), PageEleveur::class, 'json');

        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        $newPageAnimal = $this->pageAnimalService->create($user);

        $animaux = $pageEleveur->getAnimaux() ?? [];
        $animaux[] = $newPageAnimal;
        $pageEleveur->setAnimaux($animaux);

        try {
            $pageEleveur = $this->pageEleveurService->commit($user, $pageEleveur);
            return new Response(self::jsonPageEleveur($pageEleveur));
        } catch (HistoryException $e) {
            return $this->createErrorResponse($e, $user, $pageEleveur);
        }
    }

    /**
     * @param HistoryException $e
     * @return Response
     * @throws HistoryException
     */
    private function createErrorResponse(HistoryException $e, User $user, PageEleveur $pageEleveur)
    {
        $this->logger->error($e->getMessage(), ['exception' => $e, 'user' => $user, 'pageEleveur' => $pageEleveur]);
        switch ($e->getCode()) {
            case HistoryException::NON_FAST_FORWARD:
                return new Response(
                    'Plusieurs éditions sont en cours, veuillez rafraichir la page.',
                    Response::HTTP_CONFLICT);
            case HistoryException::DROIT_REFUSE:
                return new Response(
                    'Vous ne pouvez pas modifier cette page. Vérifiez que vous êtes bien connecté.',
                    Response::HTTP_FORBIDDEN);
            case HistoryException::BRANCHE_INCONNUE:
                return new Response(
                    'La page a été supprimée.',
                    Response::HTTP_NOT_FOUND);
        }
        throw $e; // @codeCoverageIgnore
    }
}