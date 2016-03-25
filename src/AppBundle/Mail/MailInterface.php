<?php


namespace AppBundle\Mail;


use Symfony\Component\EventDispatcher\Event;

interface MailInterface
{
    /** @return string */
    public function event();

    /** @return array */
    public function context(Event $event);

    /** @return string */
    public function subject(Event $event);

    /** @return string */
    public function from(Event $event);

    /** @return string[] */
    public function to(Event $event);

    /** @return string */
    public function template(Event $event);
}