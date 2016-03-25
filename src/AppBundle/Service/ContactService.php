<?php


namespace AppBundle\Service;


use AppBundle\Entity\Contact;
use AppBundle\Event\ContactEvent;
use AppBundle\Event\ZigotooEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContactService
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Logger */
    private $logger;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Logger $logger
    ) {

        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function record(Contact $contact)
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        try {
            $this->eventDispatcher->dispatch(ZigotooEvent::CONTACT, new ContactEvent($contact));
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            $this->logger->critical('', [
                'exception' => $e,
                'contact' => $contact
            ]);
        }
        // @codeCoverageIgnoreEnd
    }
}