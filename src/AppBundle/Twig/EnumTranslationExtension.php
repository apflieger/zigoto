<?php


namespace AppBundle\Twig;


use AppBundle\Entity\PageAnimal;
use Twig_Extension;
use Twig_SimpleFilter;

class EnumTranslationExtension extends Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "enum_translation_extension";
    }

    public function getFilters()
    {
        return [new Twig_SimpleFilter('translate_page_animal_statut', function($statut) {
            switch($statut) {
                case PageAnimal::DISPONIBLE:
                    return 'Disponible';
                case PageAnimal::OPTION:
                    return 'Option';
                case PageAnimal::RESERVE:
                    return 'Réservé';
            }
        })];
    }

}