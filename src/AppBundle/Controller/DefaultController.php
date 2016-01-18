<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Entity\User;
use AppBundle\Repository\PageEleveurRepository;
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

        /** @var PageEleveurRepository $pageEleveurRepository */
        $pageEleveurRepository = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:PageEleveur');
        $pageEleveur = $pageEleveurRepository->findByOwner($user);

        if (!$pageEleveur){
            /** @var FormFactory $formFactory */
            $formFactory = $this->get('form.factory');

            $form = $formFactory->createNamedBuilder('creation-page-eleveur')
                ->add('nom', 'text')
                ->add('save', 'submit', array('label' => 'Créer ma page éleveur'))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var PageEleveurService $pageEleveurService */
                $pageEleveurService = $this->container->get('zigoto.page_eleveur');

                $nom = $form->getData()['nom'];
                try {
                    $slug = $pageEleveurService->create($nom, $user)->getSlug();
                    return $this->redirectToRoute('getPageEleveur', ['pageEleveurSlug' => $slug]);
                } catch (DisplayableException $e) {
                    return new Response($e->getMessage(), Response::HTTP_CONFLICT);
                } catch (HistoryException $e) {
                    switch ($e->getCode()) {
                        case HistoryException::NOM_INVALIDE:
                            return new Response('Le nom n\'"'.$nom.'"est pas valide', Response::HTTP_NOT_ACCEPTABLE);
                    }
                }
            }

            return $this->render('index-new-eleveur.html.twig', [
                'username' => $user->getUserName(),
                'creationPageEleveur' => $form->createView()
            ]);
        }

        return $this->render('index-eleveur.html.twig', [
            'username' => $user->getUserName(),
            'pageEleveur' => $pageEleveur
        ]);

    }
}
