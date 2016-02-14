<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 14/02/2016
 * Time: 13:27
 */

namespace AppBundle\Twig;


use Twig_Compiler;
use Twig_Node;
use Twig_Node_Expression;
use Twig_NodeOutputInterface;

class TwigNodeInject extends Twig_Node implements Twig_NodeOutputInterface
{
    /**
     * Clé du paramètre de template determinant les fragments qui vont être injectés
     */
    const INJECT_PARAMETER = 'inject';

    public function __construct(Twig_Node_Expression $expr, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'variables' => null), array('only' => false, 'ignore_missing' => false), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write("try {\n")
            ->indent()
        ;
        $compiler
            ->write('$this->loadTemplate(')
            ->subcompile($this->getNode('expr'))
            ->raw('. "/" . $context[\'' . static::INJECT_PARAMETER . '\'] . ".html.twig", ')
            ->repr($compiler->getFilename())
            ->raw(', ')
            ->repr($this->getLine())
            ->raw(')')
        ;

        $compiler->raw('->display($context);' . "\n");

        $compiler
            ->outdent()
            ->write("} catch (Twig_Error_Loader \$e) {\n")
            ->indent()
            ->write("// ignore missing template\n")
            ->outdent()
            ->write("}\n\n")
        ;
    }
}