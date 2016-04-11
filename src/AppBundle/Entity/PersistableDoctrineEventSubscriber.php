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
            if ($entity->getId() !== null)
                throw new \Exception('entité '. get_class($entity) . ' deja persistée : ' . $entity->getId()); // @codeCoverageIgnore

            $now = $this->timeService->now();
            $entity->setCreatedAt($now);
            $entity->setModifiedAt($now);

            if ($entity instanceof IdentityPersistableInterface) {
                $entity->setId(bin2hex(random_bytes(8))); // Ce calcul prend 4 μs sur mon macbook
            } else if ($entity instanceof StatePersistableInterface) {
                $entity->setId($entity->hashCode());
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof IdentityPersistableInterface) {
            /** @var IdentityPersistableInterface $entity */
            $entity->setModifiedAt($this->timeService->now());
        // @codeCoverageIgnoreStart
        } else if ($entity instanceof StatePersistableInterface) {
            throw new \Exception('Une instance de ' . StatePersistableInterface::class .
                ' ne doit pas être modifiée : ' . $entity);
        }
        // @codeCoverageIgnoreEnd
    }
}