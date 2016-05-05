<?php

namespace AppBundle\Repository;

use AppBundle\Entity\PageAnimal;
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
        $queryBuilder = $this->joinQuery();
        $queryBuilder->where($queryBuilder->expr()->eq('e.slug', ':slug'));

        $queryBuilder->setParameter('slug', $slug);
        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $queryBuilder->getQuery()->getOneOrNullResult();

        return $pageEleveur;
    }

    /**
     * @param User $user
     * @return PageEleveurBranch
     */
    public function findByOwner(User $user)
    {
        $queryBuilder = $this->joinQuery();
        $queryBuilder->where($queryBuilder->expr()->eq('e.owner', ':owner'));

        $queryBuilder->setParameter('owner', $user);
        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $queryBuilder->getQuery()->getOneOrNullResult();

        return $pageEleveur;
    }

    /**
     * @param $pageAnimal
     * @return PageEleveurBranch
     */
    public function findByPageAnimal(PageAnimal $pageAnimal)
    {

        $queryBuilder = $this->joinQuery();
        $queryBuilder->where($queryBuilder->expr()->eq('a.id', ':animal_id'));

        $queryBuilder->setParameter('animal_id', $pageAnimal->getId());
        /** @var PageEleveurBranch $pageEleveur */
        $pageEleveur = $queryBuilder->getQuery()->getOneOrNullResult();

        return $pageEleveur;
    }

    private function joinQuery() {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('e', 'c', 'a', 'ac', 'pe_actualites')
            ->from('AppBundle:PageEleveurBranch', 'e')
            ->leftJoin('e.commit', 'c')
            ->leftJoin('c.actualites', 'pe_actualites')
            ->leftJoin('c.animaux', 'a')
            ->leftJoin('a.commit', 'ac')
        ;

        return $queryBuilder;
    }
}
