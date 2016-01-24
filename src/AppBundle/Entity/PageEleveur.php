<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 19/01/2016
 * Time: 23:37
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;


class PageEleveur extends Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $slug;

    /**
     * @Exclude
     * @var User
     */
    private $owner;

    /**
     * @Type("string")
     * @var string
     */
    private $nom;

    /**
     * @Type("string")
     * @var string
     */
    private $description;

    /**
     * @Type("array<AppBundle\Entity\PageAnimal>")
     * @var PageAnimal[]
     */
    private $animaux;

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param PageAnimal[] $animaux
     */
    public function setAnimaux($animaux)
    {
        $this->animaux = $animaux;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return PageAnimal[]
     */
    public function getAnimaux()
    {
        return $this->animaux;
    }
}