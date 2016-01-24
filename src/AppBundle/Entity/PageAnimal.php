<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:45
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Type;


class PageAnimal extends Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $nom;

    /**
     * @Exclude
     * @var User
     */
    private $owner;

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

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }
}