<?php

namespace AppBundle\Repository;
use AppBundle\Entity\PageAnimal;
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

    /**
     * @param $pageAnimal
     * @return PageEleveurBranch
     */
    public function findByPageAnimal(PageAnimal $pageAnimal)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT e,c,a FROM AppBundle:PageEleveurBranch e JOIN e.commit c JOIN c.animaux a WHERE a.id = ?1');

        $query->setParameter(1, $pageAnimal->getId());

        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $query->getOneOrNullResult();

        return $pageEleveur;
    }
}
