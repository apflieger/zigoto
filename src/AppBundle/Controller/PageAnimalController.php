<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 21:49
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PageAnimalController extends Controller
{
    /**
     * @Route("/animal/{pageAnimalSlug}", name="getPageAnimal")
     * @Method("GET")
     */
    public function getAction($pageAnimalSlug)
    {
        return new Response();
    }
}