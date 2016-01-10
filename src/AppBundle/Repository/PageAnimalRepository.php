<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 23:43
 */

namespace AppBundle\Repository;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PageAnimalRepository extends EntityRepository
{

    public function findByOwner(User $user)
    {
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->findOneBy(['owner' => $user]);

        return $pageAnimal;
    }
}