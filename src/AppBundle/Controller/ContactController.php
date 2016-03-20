<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Entity\User;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Route(service="zigotoo.contact_controller")
 */
class ContactController
{
    /** @var TwigEngine */
    private $templating;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var EntityRepository */
    private $entityManager;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        TwigEngine $templating,
        FormFactoryInterface $formFactory,
        TokenStorage $tokenStorage,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ) {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->router = $router;
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
            ->add('email', TextType::class)
            ->add('message', TextType::class)
            ->add('submit', SubmitType::class, array('label' => 'Envoyer'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush($contact);
            return new RedirectResponse($this->router->generate('teaser_route'));
        }


        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'contact',
            'form' => $form->createView()
        ]);
    }
}