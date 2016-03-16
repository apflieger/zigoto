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

class TwigNodeTemplateTreeSection extends Twig_Node implements Twig_NodeOutputInterface
{
    /**
     * Clé du paramètre de template determinant les fragments qui vont être injectés
     */
    const TEMPLATE_TREE_BRANCH = 'template_tree_branch';
    /**
     * @var string
     */
    private $injectedSection;
    /**
     * @var bool
     */
    private $optional;

    public function __construct(string $injectedSection, bool $optional, $lineno, $tag = null)
    {
        parent::__construct(array(), array(), $lineno, $tag);
        $this->injectedSection = $injectedSection;
        $this->optional = $optional;
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
            ->write('if (!isset($context[\'' . static::TEMPLATE_TREE_BRANCH . '\']))' . "\n")
            ->indent()
            ->write('throw new Twig_Error_Loader(\'Paramètre ' . static::TEMPLATE_TREE_BRANCH . ' manquant\');' . "\n\n")
            ->outdent()
            ->write('$template = ')
            ->repr($compiler->getFilename())
            ->raw(';' . "\n")
            ->write('$branch = $context[\'' . static::TEMPLATE_TREE_BRANCH . '\'];' . "\n")
            ->write('$dir = $branch;' . "\n")
            ->write('$injectedTemplate = "'. $this->injectedSection .'.html.twig";' . "\n\n")
            ->write('$slashpos = strlen($dir);' . "\n")
            ->write('while ($slashpos) {' . "\n")
            ->indent()
            ->write('$dir = substr($dir, 0, $slashpos);' . "\n")
            ->write('try {' . "\n")
            ->indent()
            ->write('$this->loadTemplate($dir . DIRECTORY_SEPARATOR . $injectedTemplate , $template, 8)->display($context);' . "\n")
            ->write('break;' . "\n")
            ->outdent()
            ->write('} catch (Twig_Error_Loader $e) {}' . "\n")
            ->write('$slashpos = strrpos($dir, DIRECTORY_SEPARATOR);' . "\n")
            ->outdent()
            ->write('}' . "\n\n");
        if (!$this->optional) {
            $compiler
                ->write('if (!$slashpos)' . "\n")
                ->indent()
                ->write('throw new Twig_Error_Loader($injectedTemplate .\' manquant pour la branche \' . $branch);' . "\n")
                ->outdent();
        }
    }
}