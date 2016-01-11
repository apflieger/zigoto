<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 04/01/2016
 * Time: 23:24
 */

namespace AppBundle\Service;


use AppBundle\Controller\DisplayableException;
use AppBundle\Entity\ERole;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Repository\PageEleveurRepository;
use Doctrine\ORM\EntityRepository;

class PageEleveurService
{
    /** @var HistoryService */
    private $history;

    /** @var PageEleveurRepository */
    private $pageEleveurRepository;

    /** @var EntityRepository */
    private $pageEleveurCommitRepository;

    public function __construct(
        HistoryService $history,
        PageEleveurRepository $pageEleveurRepository,
        EntityRepository $pageEleveurCommitRepository)
    {
        $this->history = $history;
        $this->pageEleveurRepository = $pageEleveurRepository;
        $this->pageEleveurCommitRepository = $pageEleveurCommitRepository;
    }

    /**
     * @param string $nom
     * @param User $owner
     * @return \AppBundle\Entity\BranchInterface|PageEleveur
     * @throws DisplayableException
     * @throws \Exception
     */
    public function create(string $nom, User $owner)
    {
        if ($this->pageEleveurRepository->findByOwner($owner))
            throw new DisplayableException('user ' . $owner->getId() . ' a deja une page eleveur');

        $commit = new PageEleveurCommit($nom, '', null);
        $pageEleveur = new PageEleveur();
        $pageEleveur->setCommit($commit);
        $pageEleveur->setOwner($owner);
        $pageEleveur->setSlug(HistoryService::slug($nom));

        if ($this->pageEleveurRepository->findBySlug($pageEleveur->getSlug()))
            throw new DisplayableException('Une page eleveur du meme nom existe deja');

        $pageEleveur = $this->history->create($pageEleveur);
        $owner->addRole(ERole::ELEVEUR);
        return $pageEleveur;
    }

    /**
     * @param User $user
     * @param int $pageEleveurId
     * @param int $currentCommitId
     * @param string $nom
     * @param string $description
     * @return PageEleveur
     * @throws HistoryException
     */
    public function commit(User $user, $pageEleveurId, $currentCommitId, $nom, $description)
    {
        /** @var PageEleveurCommit $pageEleveur */
        $pageEleveurCommit = $this->pageEleveurCommitRepository->find($currentCommitId);

        $commit = new PageEleveurCommit($nom, $description, $pageEleveurCommit);

        $this->history->commit($pageEleveurId, $commit, $user);

        /** @var PageEleveur $pageEleveur */
        $pageEleveur = $this->pageEleveurRepository->find($pageEleveurId);

        return $pageEleveur;
    }
}