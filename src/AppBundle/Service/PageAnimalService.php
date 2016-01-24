<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:52
 */

namespace AppBundle\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalBranch;
use AppBundle\Entity\PageAnimalCommit;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class PageAnimalService
{
    /** @var EntityManager */
    private $doctrine;

    /** @var PageAnimalBranchRepository */
    private $pageAnimalBranchRepository;

    public function __construct(
        EntityManager $doctrine,
        PageAnimalBranchRepository $pageAnimalBranchRepository
    ) {
        $this->doctrine = $doctrine;
        $this->pageAnimalBranchRepository = $pageAnimalBranchRepository;
    }

    public function find($pageAnimalId)
    {
        /** @var PageAnimalBranch $branch */
        $branch = $this->pageAnimalBranchRepository->find($pageAnimalId);

        if (!$branch)
            return null;

        $pageAnimal = new PageAnimal();
        $pageAnimal->setId($branch->getId());
        $pageAnimal->setHead($branch->getCommit()->getId());
        $pageAnimal->setOwner($branch->getOwner());
        $pageAnimal->setNom($branch->getCommit()->getNom());

        return $pageAnimal;
    }

    /**
     * @param User $owner
     * @return PageAnimal
     * @throws HistoryException
     */
    public function create(User $owner)
    {
        $pageAnimalBranch = new PageAnimalBranch();
        $pageAnimalBranch->setOwner($owner);
        //mettre un generateur de nom marrant
        $pageAnimalBranch->setCommit(new PageAnimalCommit(null, 'tmp'));

        $this->doctrine->persist($pageAnimalBranch->getCommit());
        $this->doctrine->persist($pageAnimalBranch);
        $this->doctrine->flush([$pageAnimalBranch->getCommit(), $pageAnimalBranch]);

        $pageAnimal = new PageAnimal();
        $pageAnimal->setId($pageAnimalBranch->getId());
        $pageAnimal->setHead($pageAnimalBranch->getCommit()->getId());
        $pageAnimal->setOwner($pageAnimalBranch->getOwner());
        $pageAnimal->setNom($pageAnimalBranch->getCommit()->getNom());
        return $pageAnimal;
    }
}