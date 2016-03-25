<?php

namespace AppBundle\Subscriber;

use AppBundle\Event\ZigotooEvent;
use AppBundle\Mail\AccuseReceptionContact;
use AppBundle\Mail\MailInterface;
use AppBundle\Mail\NotificationAdminContact;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailSubscriber implements EventSubscriberInterface
{
    /** @var Swift_Mailer */
    private $mailer;
    /** @var Logger */
    private $logger;
    /** @var TwigEngine */
    private $templating;
    /** @var MailInterface[] */
    private $emails;

    public static function getSubscribedEvents()
    {
        return [
            ZigotooEvent::CONTACT => 'onZigotooEvent'
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

        $this->emails  = [
            new AccuseReceptionContact(),
            new NotificationAdminContact()
        ];
    }

    public function onZigotooEvent(ZigotooEvent $event)
    {
        foreach ($this->emails as $mail) {
            if ($mail->event() === $event->event()) {
                try {
                    $swiftMessage = Swift_Message::newInstance()
                        ->setSubject($mail->subject($event))
                        ->setFrom($mail->from($event))
                        ->setTo($mail->to($event))
                        ->setBody(
                            $this->templating->render(
                                $mail->template($event),
                                $mail->context($event)
                            ), 'text/plain'
                        );

                    /** @var \Swift_Mime_Message $swiftMessage */
                    $this->mailer->send($swiftMessage);

                    $this->logger->info('Mail envoyé', [
                            'email' => get_class($mail),
                            'to' => $mail->to($event),
                            'from' => $mail->from($event),
                            'template' => $mail->template($event),
                        ]
                    );
                    // @codeCoverageIgnoreStart
                } catch (Exception $e) {
                    $this->logger->error('Echec d\'envoi de mail', [
                            'email' => get_class($mail),
                            'to' => $mail->to($event),
                            'from' => $mail->from($event),
                            'template' => $mail->template($event),
                            'exception' => $e
                        ]
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        }
    }
}