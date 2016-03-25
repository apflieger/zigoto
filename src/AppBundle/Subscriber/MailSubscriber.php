<?php

namespace AppBundle\Subscriber;

use AppBundle\Event\ContactEvent;
use AppBundle\Event\Events;
use AppBundle\Service\MailService;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailSubscriber implements EventSubscriberInterface
{
    /** @var Swift_Mailer */
    private $mailer;
    /** @var Logger */
    private $logger;
    /** @var TwigEngine */
    private $templating;

    public static function getSubscribedEvents()
    {
        return [
            Events::CONTACT => 'onContact'
        ];
    }

    public function __construct(
        Swift_Mailer $mailer,
        Logger $logger,
        TwigEngine $templating
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->templating = $templating;
    }

    public function onContact(ContactEvent $contactEvent)
    {
        $contact = $contactEvent->getContact();
        try {
            $accuseReception = Swift_Message::newInstance()
                ->setSubject('Formulaire de contact')
                ->setFrom('no-reply@zigotoo.com', 'Zigotoo')
                ->setTo($contact->getEmail())
                ->setBody(
                    $this->templating->render(
                        'contact/email-confirmation-contact.txt.twig',
                        ['message' => $contact->getMessage()]
                    ), 'text/plain'
                );

            /** @var \Swift_Mime_Message $accuseReception */
            $this->mailer->send($accuseReception);
            $this->logger->info('Accusé reception envoyé à ' . $contact->getEmail());

            $messageAdmin = Swift_Message::newInstance()
                ->setSubject('Reception formulaire de contact')
                ->setFrom($contact->getEmail())
                ->setTo(['pflieger.arnaud@gmail.com', 'MehdiBelkacemi@gmail.com'])
                ->setBody($contact->getMessage(), 'text/plain');

            /** @var \Swift_Mime_Message $messageAdmin */
            $this->mailer->send($messageAdmin);
            $this->logger->info('Message de ' . $contact->getEmail() . ' envoyé à ' . implode(';', array_keys($messageAdmin->getTo())));
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            $this->logger->error('Echec d\'envoi de mail du formulaire de contact', [
                    'email' => $contact->getEmail(),
                    'message' => $contact->getMessage(),
                    'exception' => $e
                ]
            );
            // @codeCoverageIgnoreEnd
        }
    }
}