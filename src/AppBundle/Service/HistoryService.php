<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 12/11/15
 * Time: 15:10
 */

namespace AppBundle\Service;


use AppBundle\Entity\BranchInterface;
use AppBundle\Entity\CommitInterface;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exception;


class HistoryService
{
    /** @var ObjectManager */
    private $doctrine;

    /** @var EntityRepository */
    private $pageEleveurRepository;

    public function __construct(EntityManager $doctrine,
                                EntityRepository $pageEleveurRepository)
    {
        $this->doctrine = $doctrine;
        $this->pageEleveurRepository = $pageEleveurRepository;
    }

    /**
     * @param BranchInterface $branch
     * @return BranchInterface
     * @throws Exception
     */
    public function create(BranchInterface $branch)
    {
        if (!$branch->getCommit())
            throw new Exception('Commit null');

        if (!$branch->getOwner())
            throw new Exception('Owner null');

        if (empty($branch->getUrl()))
            throw new Exception('Slug null');

        $this->doctrine->persist($branch);
        $this->doctrine->persist($branch->getCommit());

        $this->doctrine->flush();

        return $branch;
    }

    /**
     * @param $pageEleveurId int
     * @param CommitInterface $commit
     * @param User $user
     * @throws PageEleveurException
     */
    public function commit($pageEleveurId, CommitInterface $commit, User $user)
    {
        /**
         * @var BranchInterface $pageEleveur
         */
        $pageEleveur = $this->pageEleveurRepository->find($pageEleveurId);

        if (!$pageEleveur)
            throw new PageEleveurException('Page eleveur n\'existe pas ' . $pageEleveurId);

        if ($pageEleveur->getCommit()->getId() !== $commit->getParent()->getId())
            throw new PageEleveurException('Commit non fast forward : ' .
                $commit->getParent()->getId() .
                ', parent à ' . $pageEleveur->getCommit()->getId());

        $this->doctrine->persist($commit);
        $pageEleveur->setCommit($commit);

        $this->doctrine->flush();
    }
}