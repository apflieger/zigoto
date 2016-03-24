<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Entity\User;
use AppBundle\Service\ContactService;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Route(service="zigotoo.contact_controller")
 */
class ContactController
{
    const FLASH_BAG_EMAIL = 'confirmation-contact-email';

    /** @var TwigEngine */
    private $templating;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /** @var Session */
    private $session;

    /** @var ContactService */
    private $contactService;

    public function __construct(
        TwigEngine $templating,
        FormFactoryInterface $formFactory,
        TokenStorage $tokenStorage,
        RouterInterface $router,
        Session $session,
        ContactService $contactService
    ) {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->session = $session;
        $this->contactService = $contactService;
    }

    /**
     * @Route("/contact", name="contact_route")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactAction(Request $request)
    {
        /** @var AnonymousToken $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        $contact = new Contact();

        if ($user !== 'anon.') {
            /** @var User $user */
            $contact->setEmail($user->getEmail());
            $contact->setUser($user);
        }

        $emailGetParam = $request->query->get('email');
        if (!empty($emailGetParam)) {
            $contact->setEmail($emailGetParam);
        }

        $form = $this->formFactory->createBuilder(FormType::class, $contact)
            ->add('email', TextType::class, ['attr' => [
                'placeholder' => 'votre@adresse.email'
            ]])
            ->add('message', TextareaType::class, ['attr' => [
                'maxlength' => 1000,
                'rows' => 10
            ]])
            ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->contactService->record($contact);
            $this->session->getFlashBag()->add(static::FLASH_BAG_EMAIL, $contact->getEmail());
            return new RedirectResponse($this->router->generate('confirmation_contact_route'));
        } else {
            return new Response(
                $this->templating->render('base.html.twig', [
                    TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'contact',
                    'form' => $form->createView()
                ]),
                $form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK
            );
        }
    }

    /**
     * @Route("/contact/confirmation", name="confirmation_contact_route")
     *
     * La confirmation sur une URL différente permet à l'utilisateur de 'back'
     * pour refaire une demande de contact
     */
    public function confirmationAction()
    {
        $email = $this->session->getFlashBag()->get(static::FLASH_BAG_EMAIL);

        if (empty($email))
            return new RedirectResponse($this->router->generate('contact_route'));

        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'contact/confirmation',
            'email' => $email[0]
        ]);
    }
}