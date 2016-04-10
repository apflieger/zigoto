<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 19/01/2016
 * Time: 23:37
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Type;


class PageEleveur extends Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $slug;

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
     * @Type("string")
     * @var string
     */
    private $especes;

    /**
     * @Type("string")
     * @var string
     */
    private $races;

    /**
     * @Type("string")
     * @var string
     */
    private $lieu;

    /**
     * @Type("array<AppBundle\Entity\PageAnimal>")
     * @var PageAnimal[]
     */
    private $animaux;

    /**
     * @Type("array<AppBundle\Entity\Actualite>")
     * @var Actualite[]
     */
    private $actualites;

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
     * @param string $especes
     */
    public function setEspeces($especes)
    {
        $this->especes = $especes;
    }

    /**
     * @param string $races
     */
    public function setRaces($races)
    {
        $this->races = $races;
    }

    /**
     * @param PageAnimal[] $animaux
     */
    public function setAnimaux($animaux)
    {
        $this->animaux = $animaux;
    }

    /**
     * @param string $lieu
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
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
     * @param Actualite[] $actualites
     */
    public function setActualites($actualites)
    {
        $this->actualites = $actualites;
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
     * @return string
     */
    public function getEspeces()
    {
        return $this->especes;
    }

    /**
     * @return string
     */
    public function getRaces()
    {
        return $this->races;
    }

    /**
     * @return PageAnimal[]
     */
    public function getAnimaux()
    {
        return $this->animaux;
    }

    /**
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * @return Actualite[]
     */
    public function getActualites()
    {
        return $this->actualites;
    }
}