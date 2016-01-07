<?php

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

class PageEleveurRepository extends EntityRepository
{
    /**
     * @param $slug string
     * @return PageEleveur
     */
    public function findBySlug($slug)
    {
        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $this->findOneBy(['slug' => $slug]);

        return $pageEleveur;
    }
}
