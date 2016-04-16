<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:45
 */

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

    const A_ADOPTER = 0;
    const OPTION = 1;
    const ADOPTE = 2;
    const REPRODUCTEUR = 3;

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