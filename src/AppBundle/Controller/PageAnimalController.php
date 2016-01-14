<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 21:49
 */

namespace AppBundle\Controller;


use AppBundle\Repository\PageAnimalRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PageAnimalController extends Controller
{
    /**
     * @Route("/animal/{pageAnimalId}", name="getPageAnimal")
     * @Method("GET")
     */
    public function getAction($pageAnimalId)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        /** @var PageAnimalRepository $pageAnimalRepository */
        $pageAnimalRepository = $entityManager->getRepository('AppBundle:PageAnimal');

        $pageAnimal = $pageAnimalRepository->find($pageAnimalId);

        if (!$pageAnimal)
            throw $this->createNotFoundException();

        return $this->render('page-animal.html.twig', [
            'pageAnimal' => $pageAnimal
        ]);
    }
}