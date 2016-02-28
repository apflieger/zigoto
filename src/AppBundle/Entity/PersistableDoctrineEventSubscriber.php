<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:31
 */

namespace AppBundle\Entity;


use AppBundle\Service\TimeService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class PersistableDoctrineEventSubscriber implements EventSubscriber
{
    /**
     * @var TimeService
     */
    private $timeService;

    public function __construct(TimeService $timeService)
    {
        $this->timeService = $timeService;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof PersistableInterface) {
            /** @var PersistableInterface $entity */

            $entity->setId(bin2hex(random_bytes(8))); // Ce calcul prend 4 μs sur mon macbook

            $now = $this->timeService->now();

            $entity->setCreatedAt($now);

            $entity->setModifiedAt($now);
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof PersistableInterface) {
            /** @var PersistableInterface $entity */

            $entity->setModifiedAt($this->timeService->now());
        }
    }
}