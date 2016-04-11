<?php


namespace AppBundle\Entity;


use DateTimeImmutable;

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
     * @return DateTimeImmutable
     */
    public function getCreatedAt();

    /**
     * @param DateTimeImmutable $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * @return DateTimeImmutable
     */
    public function getModifiedAt();

    /**
     * @param DateTimeImmutable $modifiedAt
     */
    public function setModifiedAt($modifiedAt);
}