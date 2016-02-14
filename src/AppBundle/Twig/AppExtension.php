<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 14/02/2016
 * Time: 01:06
 */

namespace AppBundle\Twig;

use Twig_Extension;

class AppExtension extends Twig_Extension
{

    public function getName()
    {
        return 'app_extension';
    }

    public function getTokenParsers()
    {
        return [new InjectTokenParser()];
    }
}