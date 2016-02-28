<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:31
 */

namespace AppBundle\Entity;


use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class PersistableDoctrineEventSubscriber implements EventSubscriber
{
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

            $now = new DateTime();

            $entity->setCreatedAt($now);

            $entity->setModifiedAt($now);
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof PersistableInterface) {
            /** @var PersistableInterface $entity */

            $entity->setModifiedAt(new DateTime());
        }
    }
}