<?php


namespace AppBundle\Mail;


use Symfony\Component\EventDispatcher\Event;

abstract class FromNoReplyMail implements MailInterface
{
    /** @return string[] */
    public final function from(Event $event)
    {
        return ['no-reply@zigotoo.com' => 'Zigotoo'];
    }
}