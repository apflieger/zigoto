<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="actualite_eleveur")
 */
class Actualite implements PersistableInterface
{
    use Persistable;

    /** @var string */
    private $contenu;

    /**
     * @param string $contenu
     */
    public function __construct($contenu)
    {
        $this->contenu = $contenu;
    }

    /**
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }
}