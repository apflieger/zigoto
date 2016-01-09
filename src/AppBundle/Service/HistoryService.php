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
    private $branchRepository;

    public function __construct(EntityManager $doctrine,
                                EntityRepository $branchRepository)
    {
        $this->doctrine = $doctrine;
        $this->branchRepository = $branchRepository;
    }

    /**
     * @param BranchInterface $branch
     * @return BranchInterface
     */
    public function create(BranchInterface $branch)
    {
        $this->doctrine->persist($branch);
        $this->doctrine->persist($branch->getCommit());

        $this->doctrine->flush();

        return $branch;
    }

    /**
     * @param $branchId int
     * @param CommitInterface $commit
     * @param User $user
     * @throws HistoryException
     */
    public function commit($branchId, CommitInterface $commit, User $user)
    {
        /**
         * @var BranchInterface $pageEleveur
         */
        $pageEleveur = $this->branchRepository->find($branchId);

        if (!$pageEleveur)
            throw new HistoryException(HistoryException::BRANCHE_INCONNUE);

        if ($pageEleveur->getOwner()->getId() !== $user->getId())
            throw new HistoryException(HistoryException::DROIT_REFUSE);

        if ($pageEleveur->getCommit()->getId() !== $commit->getParent()->getId())
            throw new HistoryException(HistoryException::NON_FAST_FORWARD);

        $this->doctrine->persist($commit);
        $pageEleveur->setCommit($commit);

        $this->doctrine->flush();
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