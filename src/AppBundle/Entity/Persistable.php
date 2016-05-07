<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Pour que ce trait soit utilisé automatiquement, il faut également implementer PersistableInterface
 */
trait Persistable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16)
     * @var string
     *
     * @JMS\Expose
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     *
     * @JMS\Exclude
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     *
     * @JMS\Exclude
     */
    protected $modifiedAt;


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param DateTime $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

}