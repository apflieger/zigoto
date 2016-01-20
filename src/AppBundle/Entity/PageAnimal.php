<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:45
 */

namespace AppBundle\Entity;


class PageAnimal extends Commitable
{
    use PageAnimalTrait;

    public function setNom($nom)
    {
        $this->nom = $nom;
    }
}