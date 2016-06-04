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
        return [
            new Twig_SimpleFilter('translate_page_animal_statut', function($statut) {
                switch($statut) {
                    case PageAnimal::DISPONIBLE:
                        return 'Disponible';
                    case PageAnimal::OPTION:
                        return 'Option';
                    case PageAnimal::RESERVE:
                        return 'Réservé';
                    case PageAnimal::ADOPTE:
                        return 'Adopté';
                }
                return "";
            }),
            new Twig_SimpleFilter('chip_page_animal_statut', function($statut) {
                switch($statut) {
                    case PageAnimal::DISPONIBLE:
                        return 'chip-valid';
                    case PageAnimal::OPTION:
                        return 'chip-warn';
                    case PageAnimal::RESERVE:
                        return 'chip-error';
                    case PageAnimal::ADOPTE:
                        return '';
                }
                return '';
            }),
            new Twig_SimpleFilter('translate_page_animal_sexe', function($statut) {
                switch($statut) {
                    case PageAnimal::MALE:
                        return 'Mâle';
                    case PageAnimal::FEMELLE:
                        return 'Femelle';
                }
                return "";
            })

        ];
    }
}