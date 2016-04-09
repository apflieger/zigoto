<?php


namespace AppBundle\Mail;


use AppBundle\Event\ContactEvent;
use AppBundle\Event\ZigotooEvent;
use Symfony\Component\EventDispatcher\Event;

class NotificationAdminContact extends ToAdminMail
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

        return [
            'from' => $contact->getEmail(),
            'text' => $contact->getMessage()
        ];
    }

    /** @return string */
    public function subject(Event $event)
    {
        return 'Reception formulaire de contact';
    }

    /** @return string */
    public function template(Event $event)
    {
        return 'contact/email-contact-notif-admin.txt.twig';
    }
}