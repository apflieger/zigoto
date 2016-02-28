<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:31
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Event\LifecycleEventArgs;

class IdGenerationListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Identifiable) {
            /** @var Identifiable $entity */
            $entity->setId(bin2hex(random_bytes(8)));
        }
    }
}