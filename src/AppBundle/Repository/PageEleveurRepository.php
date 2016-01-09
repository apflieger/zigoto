<?php

namespace AppBundle\Repository;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PageEleveurRepository extends EntityRepository
{
    /**
     * @param $slug string
     * @return PageEleveur
     */
    public function findBySlug($slug)
    {
        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $this->findOneBy(['slug' => $slug]);

        return $pageEleveur;
    }

    public function findByOwner(User $user)
    {
        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $this->findOneBy(['owner' => $user]);

        return $pageEleveur;
    }
}
