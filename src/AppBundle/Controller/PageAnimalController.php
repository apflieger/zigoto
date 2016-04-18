<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 21:49
 */

namespace AppBundle\Controller;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\User;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageAnimalService;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use JMS\Serializer\Serializer;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Route(service="zigotoo.page_animal_controller")
 */
class PageAnimalController
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var TwigEngine */
    private $templating;

    /** @var RouterInterface */
    private $router;

    /** @var Serializer */
    private $serializer;

    /** @var PageAnimalService */
    private $pageAnimalService;

    public function __construct(
        TokenStorage $tokenStorage,
        TwigEngine $templating,
        RouterInterface $router,
        Serializer $serializer,
        PageAnimalService $pageAnimalService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->router = $router;
        $this->serializer = $serializer;
        $this->pageAnimalService = $pageAnimalService;
    }

    /**
     * @Route("/animal/{pageAnimalId}", name="getPageAnimal_route")
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($pageAnimalId)
    {
        $pageAnimal = $this->pageAnimalService->find($pageAnimalId);

        if (!$pageAnimal)
            throw new NotFoundHttpException(null, null);

        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();
        $isOwner = $user !== 'anon.' && $pageAnimal->getOwner()->getId() === $user->getId();

        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'editable/page-animal',
            'pageAnimal' => $pageAnimal,
            'isEditable' => $isOwner
        ]);
    }

    /**
     * @Route("/animal/{pageAnimalId}", name="commitPageAnimal_route")
     * @Method("POST")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function commitAction($pageAnimalId, Request $request)
    {
        /** @var PageAnimal $pageEleveur */
        $pageAnimal = $this->serializer->deserialize($request->getContent(), PageAnimal::class, 'json');

        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if ($user === 'anon.') {
            return new RedirectResponse($this->router->generate('fos_user_security_login'));
        }

        try {
            $this->pageAnimalService->commit($user, $pageAnimal);
            return new Response($this->serializer->serialize($pageAnimal, 'json'));
        } catch (HistoryException $e) {
            return $this->createErrorResponse($e);
        }
    }

    /**
     * @param HistoryException $e
     * @return Response
     * @throws HistoryException
     */
    private function createErrorResponse(HistoryException $e)
    {
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