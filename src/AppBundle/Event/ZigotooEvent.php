<?php


namespace AppBundle\Event;


use Symfony\Component\EventDispatcher\Event;

abstract class ZigotooEvent extends Event
{
    const CONTACT = "zigotoo.contact";

    /** @return string */
    public abstract function event();
}