<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageEleveurService;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Route(service="zigotoo.default_controller")
 */
class DefaultController
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;
    /**
     * @var TwigEngine
     */
    private $templating;
    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var PageEleveurService
     */
    private $pageEleveurService;

    public function __construct(
        TokenStorage $tokenStorage,
        TwigEngine $templating,
        FormFactory $formFactory,
        Router $router,
        PageEleveurService $pageEleveurService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->pageEleveurService = $pageEleveurService;
    }

    /**
     * @Route("/", name="teaser_route")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function teaserAction(Request $request)
    {
        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if ($user == 'anon.')
            return $this->templating->renderResponse('base.html.twig', [
                TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'home/teaser'
            ]);

        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'home/teaser_logged_in',
            'user' => $user
        ]);
    }
}
