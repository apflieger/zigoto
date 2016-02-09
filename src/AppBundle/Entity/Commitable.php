<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:24
 */

namespace AppBundle\Entity;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;


class Commitable
{
    /**
     * @Type("string")
     * @var string
     */
    private $id;

    /**
     * @Type("string")
     * @var string
     */
    private $head;

    /**
     * @Exclude
     * @var User
     */
    private $owner;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setHead($head)
    {
        $this->head = $head;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}