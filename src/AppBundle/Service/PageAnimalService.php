<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:52
 */

namespace AppBundle\Service;


use AppBundle\Controller\DisplayableException;
use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\User;

class PageAnimalService
{

    public function create($nom, User $owner)
    {
        $pageAnimal = new PageAnimal();
        $pageAnimal->setSlug(HistoryService::slug($nom));
        return $pageAnimal;
    }
}