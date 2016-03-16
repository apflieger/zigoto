<?php

namespace AppBundle\Twig;

use Twig_Extension;

class TemplateTreeExtension extends Twig_Extension
{

    public function getName()
    {
        return 'template_tree_extension';
    }

    public function getTokenParsers()
    {
        return [new TemplateTreeSectionTokenParser()];
    }
}