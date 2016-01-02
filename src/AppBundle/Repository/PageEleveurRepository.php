<?php

namespace AppBundle\Repository;
use Doctrine\ORM\EntityRepository;

class PageEleveurRepository extends EntityRepository
{
    /**
     * @param $url string
     * @return PageEleveur
     */
    public function findByUrl($url)
    {
        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $this->findOneBy(['url' => $url]);

        return $pageEleveur;
    }
}
