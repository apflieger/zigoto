<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ERole;
use AppBundle\Service\PageEleveurException;
use AppBundle\Service\PageEleveurService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
         */
        $tokenStorage = $this->container->get('security.token_storage');

        $user = $tokenStorage->getToken()->getUser();
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
                 * @var $pageEleveurService PageEleveurService
                 */
                $pageEleveurService = $this->container->get('page_eleveur');

                try {
                    $url = $pageEleveurService->create($form->getData()['nom'], $user, $user)->getUrl();
                } catch (PageEleveurException $e) {
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
