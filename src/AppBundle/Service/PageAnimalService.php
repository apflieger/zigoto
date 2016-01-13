<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:52
 */

namespace AppBundle\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalCommit;
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
     * @param User $owner
     * @return PageAnimal
     * @throws HistoryException
     */
    public function create(User $owner)
    {
        $pageAnimal = new PageAnimal();
        $pageAnimal->setOwner($owner);
        //mettre un generateur de nom marrant
        $pageAnimal->setCommit(new PageAnimalCommit(null, 'tmp'));

        $this->historyService->create($pageAnimal);

        return $pageAnimal;
    }
}