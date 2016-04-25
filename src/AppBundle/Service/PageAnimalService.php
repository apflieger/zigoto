<?php

namespace AppBundle\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalBranch;
use AppBundle\Entity\PageAnimalCommit;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Config\FileLocator;

class PageAnimalService
{
    /** @var EntityManager */
    private $doctrine;

    /** @var PageAnimalBranchRepository */
    private $pageAnimalBranchRepository;

    /** @var EntityRepository */
    private $pageAnimalCommitRepository;

    /** @var FileLocator */
    private $fileLocator;

    /** @var TimeService */
    private $timeService;

    public function __construct(
        EntityManager $doctrine,
        PageAnimalBranchRepository $pageAnimalBranchRepository,
        EntityRepository $pageAnimalCommitRepository,
        FileLocator $fileLocator,
        TimeService $timeService
    ) {
        $this->doctrine = $doctrine;
        $this->pageAnimalBranchRepository = $pageAnimalBranchRepository;
        $this->pageAnimalCommitRepository = $pageAnimalCommitRepository;
        $this->fileLocator = $fileLocator;
        $this->timeService = $timeService;
    }

    public function find($pageAnimalId)
    {
        /** @var PageAnimalBranch $branch */
        $branch = $this->pageAnimalBranchRepository->find($pageAnimalId);

        if (!$branch)
            return null;

        return $this->fromBranch($branch);
    }

    private function fromBranch(PageAnimalBranch $branch)
    {
        $pageAnimal = new PageAnimal();
        $pageAnimal->setId($branch->getId());
        $pageAnimal->setHead($branch->getCommit()->getId());
        $pageAnimal->setOwner($branch->getOwner());
        $pageAnimal->setNom($branch->getCommit()->getNom());
        $pageAnimal->setDateNaissance($branch->getCommit()->getDateNaissance());
        $pageAnimal->setDescription($branch->getCommit()->getDescription());
        $pageAnimal->setStatut($branch->getCommit()->getStatut());
        $pageAnimal->setPhotos($branch->getCommit()->getPhotos()->toArray());

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

        $noms = file($this->fileLocator->locate('@AppBundle/Resources/noms-animaux/noms.txt'));

        $nom = trim($noms[rand(0, count($noms) - 1)]);
        $pageAnimalBranch->setCommit(new PageAnimalCommit(
            null,
            $nom,
            $this->timeService->now(),
            null,
            PageAnimal::DISPONIBLE,
            null
        ));

        $this->doctrine->persist($pageAnimalBranch->getCommit());
        $this->doctrine->persist($pageAnimalBranch);
        $this->doctrine->flush([$pageAnimalBranch->getCommit(), $pageAnimalBranch]);

        return $this->fromBranch($pageAnimalBranch);
    }

    /**
     * @param User $user
     * @param PageAnimal $pageAnimal
     * @throws HistoryException
     * @throws ValidationException
     */
    public function commit(User $user, PageAnimal $pageAnimal)
    {
        /** @var PageAnimalBranch $pageAnimalBranch */
        $pageAnimalBranch = $this->pageAnimalBranchRepository->find($pageAnimal->getId());

        if ($pageAnimalBranch == null)
            throw new HistoryException(HistoryException::BRANCHE_INCONNUE);

        if ($user->getId() !== $pageAnimalBranch->getOwner()->getId())
            throw new HistoryException(HistoryException::DROIT_REFUSE);

        /** @var PageAnimalCommit $clientHead */
        $clientHead = $this->pageAnimalCommitRepository->find($pageAnimal->getHead());

        if ($clientHead->getId() !== $pageAnimalBranch->getCommit()->getId())
            throw new HistoryException(HistoryException::NON_FAST_FORWARD);

        if (empty($pageAnimal->getNom()))
            throw new ValidationException(ValidationException::EMPTY_NOM);

        if (empty($pageAnimal->getDateNaissance()))
            throw new ValidationException(ValidationException::EMPTY_DATE_NAISSANCE);

        $commit = new PageAnimalCommit(
            $clientHead,
            $pageAnimal->getNom(),
            $pageAnimal->getDateNaissance(),
            $pageAnimal->getDescription(),
            $pageAnimal->getStatut(),
            $pageAnimal->getPhotos()
        );
        
        $this->doctrine->persist($commit);
        $pageAnimalBranch->setCommit($commit);
        $this->doctrine->flush([$commit, $pageAnimalBranch]);

        $pageAnimal->setHead($commit->getId());
    }
}