<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 01/11/15
 * Time: 20:26
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CreationPageEleveurController extends Controller
{
    /**
     * @Route("/creation-page-eleveur")
     * @Method("POST")
     */
    public function creationPageEleveurAction()
    {
        return new Response("woot");
    }
}