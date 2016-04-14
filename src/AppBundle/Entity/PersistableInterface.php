<?php


namespace AppBundle\Entity;


use DateTime;

interface PersistableInterface
{
    /**
     * @param string $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * @return DateTime
     */
    public function getModifiedAt();

    /**
     * @param DateTime $modifiedAt
     */
    public function setModifiedAt($modifiedAt);
}