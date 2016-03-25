<?php


namespace AppBundle\Mail;


use Symfony\Component\EventDispatcher\Event;

abstract class ToAdminMail implements MailInterface
{
    public final function to(Event $event)
    {
        return ['pflieger.arnaud@gmail.com', 'MehdiBelkacemi@gmail.com'];
    }
}