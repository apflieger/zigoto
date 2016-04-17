<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 21:49
 */

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Service\PageAnimalService;
use AppBundle\Twig\TwigNodeTemplateTreeSection;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /** @var PageAnimalService */
    private $pageAnimalService;

    public function __construct(
        TokenStorage $tokenStorage,
        TwigEngine $templating,
        PageAnimalService $pageAnimalService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->pageAnimalService = $pageAnimalService;
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

        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'editable/page-animal',
            'pageAnimal' => $pageAnimal,
            'isEditable' => $isOwner
        ]);
    }
}