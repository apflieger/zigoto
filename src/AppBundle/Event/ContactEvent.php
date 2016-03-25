<?php


namespace AppBundle\Event;


use AppBundle\Entity\Contact;
use Symfony\Component\EventDispatcher\Event;

class ContactEvent extends ZigotooEvent
{
    /** @var Contact */
    private $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /** @return string */
    public function event()
    {
        return ZigotooEvent::CONTACT;
    }
}