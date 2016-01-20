<?php

namespace AppBundle\Repository;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PageEleveurBranchRepository extends EntityRepository
{
    /**
     * @param $slug string
     * @return PageEleveurBranch
     */
    public function findBySlug($slug)
    {
        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $this->findOneBy(['slug' => $slug]);

        return $pageEleveur;
    }

    /**
     * @param User $user
     * @return PageEleveurBranch
     */
    public function findByOwner(User $user)
    {
        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $this->findOneBy(['owner' => $user]);

        return $pageEleveur;
    }
}
