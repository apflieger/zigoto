<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:45
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Type;


class PageAnimal extends Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $nom;

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }
}