<?php


namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Type;

class Photo
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