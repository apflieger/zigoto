<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:53
 */

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Persistable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
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