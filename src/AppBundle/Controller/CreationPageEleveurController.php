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
 * @Route(service="zigotoo.creation_page_eleveur_controller")
 */
class CreationPageEleveurController
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
     * @Route("/creation-page-eleveur", name="creationPageEleveur_route")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function creationPageEleveurAction(Request $request)
    {
        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        $pageEleveur = $this->pageEleveurService->findByOwner($user);

        $form = $this->formFactory->createNamedBuilder('creation-page-eleveur')
            ->add('nom', TextType::class, ['label' => 'Nom de l\'élevage'])
            ->add('save', SubmitType::class, array('label' => 'Créer ma page éleveur'))
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted() && $pageEleveur){
            // un eleveur ne peux pas créer une 2eme page eleveur
            return $this->templating->renderResponse('base.html.twig', [
                TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'creation-page-eleveur/deja-eleveur',
                'pageEleveur' => $pageEleveur
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // traitement du formulaire de creation de page eleveur
            $nom = $form->getData()['nom'];
            try {
                $slug = $this->pageEleveurService->create($nom, $user)->getSlug();
                return new RedirectResponse($this->router->generate('getPageEleveur_route', ['pageEleveurSlug' => $slug]));
            } catch (HistoryException $e) {
                switch ($e->getCode()) {
                    case HistoryException::NOM_INVALIDE:
                        return new Response('Le nom "'.$nom.'" n\'est pas valide.', Response::HTTP_NOT_ACCEPTABLE);
                    case HistoryException::SLUG_DEJA_EXISTANT:
                        return new Response('Une page éleveur du même nom existe déjà.', Response::HTTP_CONFLICT);
                    case HistoryException::DEJA_OWNER:
                        return new Response('Vous avez déjà une page éleveur.', Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // home d'un user connecté mais qui n'a pas de page eleveur
        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'creation-page-eleveur',
            'creationPageEleveur' => $form->createView()
        ]);
    }
}