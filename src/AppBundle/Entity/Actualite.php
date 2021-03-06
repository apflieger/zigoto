<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur_actualite")
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
     * @Type("DateTime")
     * @ORM\Column(type="datetime")
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

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function hashCode()
    {
        return substr(md5(serialize([$this->contenu, $this->date->getTimestamp()])), 0, 16);
    }
}