<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 01/03/16
 * Time: 00:51
 */

namespace AppBundle\Controller;

use AppBundle\Twig\TwigNodeTemplateTreeSection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\TwigBundle\TwigEngine;


/**
 * @Route(service="zigotoo.quisommesnous_controller")
 */
class QuiSommesNousController
{
    /**
     * @var TwigEngine
     */
    private $templating;

    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @Route("/qui-sommes-nous", name="quisommesnous")
     */
    public function quiSommesNousAction()
    {
        return $this->templating->renderResponse('base.html.twig', [
            TwigNodeTemplateTreeSection::TEMPLATE_TREE_BRANCH => 'qui-sommes-nous'
        ]);
    }
}