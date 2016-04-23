<?php

namespace AppBundle\Entity;

use DateTime;
use JMS\Serializer\Annotation\Type;


class PageAnimal extends Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $nom;

    /**
     * @Type("DateTime")
     * @var DateTime
     */
    private $dateNaissance;

    /**
     * @Type("string")
     * @var string
     */
    private $description;

    const DISPONIBLE = 1;
    const OPTION = 2;
    const RESERVE = 3;
    const ADOPTE = 4;

    /**
     * @Type("integer")
     * @var int
     */
    private $statut;

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
     * @return DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @param DateTime $dateNaissance
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * @param int $statut
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;
    }
}