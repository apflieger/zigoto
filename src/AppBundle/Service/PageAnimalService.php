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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class PageAnimalService
{
    /** @var PageAnimalRepository */
    private $pageAnimalRepository;

    /** @var HistoryService */
    private $historyService;

    public function __construct(PageAnimalRepository $pageAnimalRepository, HistoryService $historyService)
    {
        $this->pageAnimalRepository = $pageAnimalRepository;
        $this->historyService = $historyService;
    }

    /**
     * @param $nom
     * @param User $owner
     * @return PageAnimal
     * @throws HistoryException
     */
    public function create($nom, User $owner)
    {
        $pageAnimal = new PageAnimal();
        $pageAnimal->setSlug(HistoryService::slug($nom));
        $pageAnimal->setOwner($owner);

        if ($this->pageAnimalRepository->findByOwnerAndSlug($pageAnimal->getOwner(), $pageAnimal->getSlug()))
            throw new HistoryException(HistoryException::SLUG_DEJA_EXISTANT);

        return $pageAnimal;
    }
}