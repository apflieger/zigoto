<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Entity\User;
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
        /**
         * @var TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');

        /**
         * @var AnonymousToken
         */
        $token = $tokenStorage->getToken();

        /**
         * @var User
         */
        $user = $token->getUser();
        if ($user == 'anon.')
            return $this->render('index.html.twig');
        else if (!$user->hasRole(ERole::ELEVEUR)){
            /**
             * @var FormFactory $formFactory
             */
            $formFactory = $this->get('form.factory');

            $form = $formFactory->createNamedBuilder('creation-page-eleveur')
                ->add('nom', 'text')
                ->add('save', 'submit', array('label' => 'Créer ma page éleveur'))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /**
                 * @var PageEleveurService $pageEleveurService
                 */
                $pageEleveurService = $this->container->get('zigoto.page_eleveur');

                try {
                    $url = $pageEleveurService->create($form->getData()['nom'], $user)->getUrl();
                } catch (DisplayableException $e) {
                    return new Response($e->getMessage(), Response::HTTP_CONFLICT);
                }

                return $this->redirectToRoute('getPageEleveur', ['eleveurURL' => $url]);
            }

            return $this->render('index-new-eleveur.html.twig', [
                'username' => $user->getUserName(),
                'creationPageEleveur' => $form->createView()
            ]);
        }
        else return $this->render('index-eleveur.html.twig', ['username' => $user->getUserName()]);
    }
}
