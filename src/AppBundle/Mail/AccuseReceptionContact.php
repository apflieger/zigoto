<?php

namespace AppBundle\Mail;

use AppBundle\Event\ContactEvent;
use AppBundle\Event\ZigotooEvent;
use Symfony\Component\EventDispatcher\Event;

class AccuseReceptionContact extends FromNoReplyMail
{

    /** @return string */
    public function event()
    {
        return ZigotooEvent::CONTACT;
    }

    /** @return array */
    public function context(Event $event)
    {
        /** @var ContactEvent $event */
        $contact = $event->getContact();

        return ['message' => $contact->getMessage()];
    }

    /** @return string */
    public function subject(Event $event)
    {
        return 'Formulaire de contact';
    }

    /** @return string[] */
    public function to(Event $event)
    {
        /** @var ContactEvent $event */
        $contact = $event->getContact();

        return $contact->getEmail();
    }

    /** @return string */
    public function template(Event $event)
    {
        return 'contact/email-confirmation-contact.txt.twig';
    }
}