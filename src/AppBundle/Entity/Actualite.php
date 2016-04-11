<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="actualite_eleveur")
 */
class Actualite implements StatePersistableInterface
{
    use Persistable;

    /**
     * @var string
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $contenu;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @param string $contenu
     * @param DateTime $date
     */
    public function __construct($contenu, DateTime $date)
    {
        $this->contenu = $contenu;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    public function hashCode()
    {
        return substr(md5(serialize([$this->contenu, $this->date])), 0, 16);
    }
}