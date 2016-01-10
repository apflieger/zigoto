<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:52
 */

namespace AppBundle\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalRepository;

class PageAnimalService
{

    /**
     * @var PageAnimalRepository
     */
    private $pageAnimalRepository;

    public function __construct(PageAnimalRepository $pageAnimalRepository)
    {
        $this->pageAnimalRepository = $pageAnimalRepository;
    }

    /**
     * @param $nom
     * @param User $owner
     * @return PageAnimal
     * @throws HistoryException
     */
    public function create(string $nom, User $owner)
    {
        $pageAnimal = new PageAnimal();
        $pageAnimal->setSlug(HistoryService::slug($nom));
        $pageAnimal->setOwner($owner);
        return $pageAnimal;
    }
}