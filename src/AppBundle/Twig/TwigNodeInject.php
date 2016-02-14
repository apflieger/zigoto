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
    /**
     * @var string
     */
    private $expr;
    /**
     * @var bool
     */
    private $optional;

    public function __construct(string $expr, bool $optional, $lineno, $tag = null)
    {
        parent::__construct(array(), array(), $lineno, $tag);
        $this->expr = $expr;
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
            ->write("try {\n")
            ->indent()
            ->write('$this->loadTemplate(\'')
            ->raw($this->expr)
            ->raw('\' . "/" . $context[\'' . static::INJECT_PARAMETER . '\'] . ".html.twig", ')
            ->repr($compiler->getFilename())
            ->raw(', ')
            ->repr($this->getLine())
            ->raw(')')
            ->raw('->display($context);' . "\n")
            ->outdent()
            ->write("} catch (Twig_Error_Loader \$e) {\n")
            ->indent();

        if ($this->optional) {
            $compiler->write("// Injection déclarée optionnelle \n");
        } else {
            $compiler->write('throw new Twig_Error_Loader(\'Injection \\\'');
            $compiler->raw($this->expr);
            $compiler->raw('\\\' manquante pour \\\'\' . ');
            $compiler->raw('$context[\'');
            $compiler->raw(static::INJECT_PARAMETER);
            $compiler->raw('\'] . \'\\\'\', -1, null, $e);');
        }

        $compiler
            ->outdent()
            ->write("}\n\n");
    }
}