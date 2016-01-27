<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Entity\User;
use AppBundle\Repository\PageEleveurBranchRepository;
use AppBundle\Service\HistoryException;
use AppBundle\Service\PageEleveurService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        /** @var TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');

        /** @var AnonymousToken $token */
        $token = $tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if ($user == 'anon.')
            return $this->render('index.html.twig');

        /** @var PageEleveurService $pageEleveurService */
        $pageEleveurService = $this->get('zigoto.page_eleveur');
        $pageEleveur = $pageEleveurService->findByOwner($user);

        if ($pageEleveur){
            return $this->render('index-eleveur.html.twig', [
                'username' => $user->getUserName(),
                'pageEleveur' => $pageEleveur
            ]);
        }

        /** @var FormFactory $formFactory */
        $formFactory = $this->get('form.factory');

        $form = $formFactory->createNamedBuilder('creation-page-eleveur')
            ->add('nom', 'text')
            ->add('save', 'submit', array('label' => 'Créer ma page éleveur'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $form->getData()['nom'];
            try {
                $slug = $pageEleveurService->create($nom, $user)->getSlug();
                return $this->redirectToRoute('getPageEleveur', ['pageEleveurSlug' => $slug]);
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

        return $this->render('index-new-eleveur.html.twig', [
            'username' => $user->getUserName(),
            'creationPageEleveur' => $form->createView()
        ]);


    }
}
