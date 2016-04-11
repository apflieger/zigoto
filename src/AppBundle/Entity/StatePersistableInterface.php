<?php


namespace AppBundle\Entity;


interface StatePersistableInterface extends PersistableInterface
{
    public function hashCode();
}