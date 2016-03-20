<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:53
 */

namespace AppBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Pour que ce trait soit utilisé automatiquement, il faut également implementer PersistableInterface
 */
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
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeImmutable
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
     * @return DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param DateTimeImmutable $modifiedAt
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

}