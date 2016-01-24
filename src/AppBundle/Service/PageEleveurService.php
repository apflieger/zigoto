<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 04/01/2016
 * Time: 23:24
 */

namespace AppBundle\Service;


use AppBundle\Controller\DisplayableException;
use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalBranch;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use AppBundle\Repository\PageEleveurBranchRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class PageEleveurService
{
    /** @var EntityManager */
    private $doctrine;

    /** @var PageEleveurBranchRepository */
    private $pageEleveurBranchRepository;

    /**  @var PageAnimalBranchRepository */
    private $pageAnimalBranchRepository;

    /** @var EntityRepository */
    private $pageEleveurCommitRepository;

    public function __construct(
        EntityManager $doctrine,
        PageEleveurBranchRepository $pageEleveurBranchRepository,
        PageAnimalBranchRepository $pageAnimalBranchRepository,
        EntityRepository $pageEleveurCommitRepository
    ) {
        $this->doctrine = $doctrine;
        $this->pageEleveurBranchRepository = $pageEleveurBranchRepository;
        $this->pageAnimalBranchRepository = $pageAnimalBranchRepository;
        $this->pageEleveurCommitRepository = $pageEleveurCommitRepository;
    }

    /**
     * @param PageEleveurBranch $branch
     * @return PageEleveur
     */
    private function fromBranch(PageEleveurBranch $branch)
    {
        $pageEleveur = new PageEleveur();
        $pageEleveur->setId($branch->getId());
        $pageEleveur->setOwner($branch->getOwner());
        $pageEleveur->setSlug($branch->getSlug());
        $pageEleveur->setHead($branch->getCommit()->getId());
        $pageEleveur->setNom($branch->getCommit()->getNom());
        $pageEleveur->setDescription($branch->getCommit()->getDescription());

        $arrayCollection = $branch->getCommit()->getAnimaux();
        $animaux = [];
        /** @var PageAnimalBranch $pageAnimalBranch */
        foreach ($arrayCollection->toArray() as $pageAnimalBranch) {
            $pageAnimal = new PageAnimal();
            $pageAnimal->setId($pageAnimalBranch->getId());
            $pageAnimal->setHead($pageAnimalBranch->getCommit()->getId());
            $pageAnimal->setNom($pageAnimalBranch->getCommit()->getNom());
            $animaux[] = $pageAnimal;
        }
        $pageEleveur->setAnimaux($animaux);

        return $pageEleveur;
    }

    /**
     * @param string $slug
     * @return PageEleveur
     */
    public function findBySlug($slug)
    {
        $pageEleveurBranch = $this->pageEleveurBranchRepository->findBySlug($slug);

        if (!$pageEleveurBranch)
            return null;

        return $this->fromBranch($pageEleveurBranch);
    }

    /**
     * @param User $owner
     * @return PageEleveur|null
     */
    public function findByOwner(User $owner)
    {
        $pageEleveurBranch = $this->pageEleveurBranchRepository->findByOwner($owner);

        if (!$pageEleveurBranch)
            return null;

        return $this->fromBranch($pageEleveurBranch);
    }
    /**
     * @param string $nom
     * @param User $owner
     * @return PageEleveur
     * @throws DisplayableException
     */
    public function create($nom, User $owner)
    {
        if ($this->pageEleveurBranchRepository->findByOwner($owner))
            throw new DisplayableException('user ' . $owner->getId() . ' a deja une page eleveur');

        $commit = new PageEleveurCommit(null, $nom, '', null);
        $pageEleveurBranch = new PageEleveurBranch();
        $pageEleveurBranch->setCommit($commit);
        $pageEleveurBranch->setSlug(static::slug($nom));
        $pageEleveurBranch->setOwner($owner);

        if ($this->pageEleveurBranchRepository->findBySlug($pageEleveurBranch->getSlug()))
            throw new DisplayableException('Une page eleveur du meme nom existe deja');

        $this->doctrine->persist($pageEleveurBranch->getCommit());
        $this->doctrine->persist($pageEleveurBranch);
        $this->doctrine->flush([$pageEleveurBranch->getCommit(), $pageEleveurBranch]);

        return $this->fromBranch($pageEleveurBranch);
    }

    /**
     * @param User $user
     * @param PageEleveur $commitingPageEleveur
     * @return PageEleveur
     * @throws HistoryException
     */
    public function commit(User $user, PageEleveur $commitingPageEleveur)
    {
        /** @var PageEleveurCommit $clientHead */
        $clientHead = $this->pageEleveurCommitRepository->find($commitingPageEleveur->getHead());

        /** @var PageEleveurBranch $pageEleveurBranch */
        $pageEleveurBranch = $this->pageEleveurBranchRepository->find($commitingPageEleveur->getId());

        if (!$pageEleveurBranch || !$clientHead)
            throw new HistoryException(HistoryException::BRANCHE_INCONNUE);

        if ($pageEleveurBranch->getOwner()->getId() !== $user->getId())
            throw new HistoryException(HistoryException::DROIT_REFUSE);

        if ($clientHead->getId() !== $pageEleveurBranch->getCommit()->getId())
            throw new HistoryException(HistoryException::NON_FAST_FORWARD);

        $newCommit = new PageEleveurCommit(
            $clientHead,
            $commitingPageEleveur->getNom(),
            $commitingPageEleveur->getDescription(),
            $commitingPageEleveur->getAnimaux() !== null ?
                array_map(
                    function(PageAnimal $pageAnimal) {
                        return $this->pageAnimalBranchRepository->find($pageAnimal->getId());
                    }, $commitingPageEleveur->getAnimaux()
                ) :
                []
        );

        $this->doctrine->persist($newCommit);
        $pageEleveurBranch->setCommit($newCommit);
        $this->doctrine->flush([$newCommit, $pageEleveurBranch]);

        $commitingPageEleveur->setHead($newCommit->getId());

        return $commitingPageEleveur;
    }

    /**
     * @param $str string
     * @return string
     * @throws HistoryException
     */
    public static function slug($str)
    {
        // conversion de tous les caractères spéciaux vers de l'ascii
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT', $str);

        // suppression de tous les caractères qui ne sont pas des chiffres, lettres, ou "_+- "
        $ascii = preg_replace('/[^a-zA-Z0-9\/_+ -]/', '', $ascii);

        // lowercase
        $ascii = strtolower($ascii);

        // remplacement de tout ce qui n'est pas chiffres ou lettres par le séparateur '-'
        $ascii = preg_replace('/[\/_+ -]+/', '-', $ascii);

        // trim
        $ascii = trim($ascii, '-');

        if (empty($ascii))
            throw new HistoryException(HistoryException::NOM_INVALIDE);

        return $ascii;
    }
}