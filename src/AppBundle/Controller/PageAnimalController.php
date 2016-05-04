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
use AppBundle\Service\PageEleveurService;
use AppBundle\Service\ValidationException;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
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

    /** @var PageEleveurService */
    private $pageEleveurService;

    /** @var Logger */
    private $logger;

    public function __construct(
        TokenStorage $tokenStorage,
        TwigEngine $templating,
        RouterInterface $router,
        Serializer $serializer,
        PageAnimalService $pageAnimalService,
        PageEleveurService $pageEleveurService,
        Logger $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->router = $router;
        $this->serializer = $serializer;
        $this->pageAnimalService = $pageAnimalService;
        $this->pageEleveurService = $pageEleveurService;
        $this->logger = $logger;
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

        $pageEleveur = $this->pageEleveurService->findByPageAnimal($pageAnimal);
        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'editable/page-animal',
            'pageAnimal' => $pageAnimal,
            'isEditable' => $isOwner,
            'pageEleveur' => $pageEleveur
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
            return $this->createHistoryErrorResponse($e, $user, $pageAnimal);
        } catch (ValidationException $e) {
            return $this->createValidationErrorResponse($e, $user, $pageAnimal);
        }
    }

    /**
     * @param HistoryException $e
     * @param User $user
     * @param PageAnimal $pageAnimal
     * @return Response
     * @throws HistoryException
     */
    private function createHistoryErrorResponse(HistoryException $e, User $user, PageAnimal $pageAnimal)
    {
        $this->logger->error($e->getMessage(), ['exception' => $e, 'user' => $user, 'pageAnimal' => $pageAnimal]);
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

    /**
     * @param ValidationException $e
     * @param User $user
     * @param PageAnimal $pageAnimal
     * @return Response
     * @throws ValidationException
     */
    private function createValidationErrorResponse(ValidationException $e, User $user, PageAnimal $pageAnimal)
    {
        $this->logger->error($e->getMessage(), ['exception' => $e, 'user' => $user, 'pageAnimal' => $pageAnimal]);
        switch ($e->getCode()) {
            case ValidationException::EMPTY_NOM:
                return new Response(
                    'L\'animal doit avoir un nom',
                    Response::HTTP_NOT_ACCEPTABLE);
            case ValidationException::EMPTY_DATE_NAISSANCE:
                return new Response(
                    'L\'animal doit avoir une date de naissance',
                    Response::HTTP_NOT_ACCEPTABLE);
        }
        throw $e; // @codeCoverageIgnore
    }
}