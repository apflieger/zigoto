<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 14/02/2016
 * Time: 01:21
 */

namespace AppBundle\Twig;


use Twig_Error_Syntax;
use Twig_Node;
use Twig_Token;
use Twig_TokenParser;

class InjectTokenParser extends Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_Node
     *
     * @throws Twig_Error_Syntax
     */
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        // Nom de l'injection dans le template
        $inject = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $optional = false;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'optional')) {
            $optional = true;
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new TwigNodeInject($inject, $optional, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return TwigNodeInject::TEMPLATE_TREE_BRANCH;
    }
}