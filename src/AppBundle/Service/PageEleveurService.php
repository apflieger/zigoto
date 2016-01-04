<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 12/11/15
 * Time: 15:10
 */

namespace AppBundle\Service;


use AppBundle\Entity\BranchInterface;
use AppBundle\Entity\ERole;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Bridge\Monolog\Logger;


class PageEleveurService
{

    /** @var ObjectManager */
    private $doctrine;

    /** @var Logger */
    private $logger;

    /** @var EntityRepository */
    private $pageEleveurRepository;

    public function __construct(EntityManager $doctrine,
                                EntityRepository $pageEleveurRepository,
                                Logger $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->pageEleveurRepository = $pageEleveurRepository;
    }

    /**
     * @param BranchInterface $branch
     * @param User $commiter
     * @return PageEleveur
     * @throws Exception
     * @throws PageEleveurException
     */
    public function create(BranchInterface $branch, User $commiter)
    {
        if (!$branch->getCommit())
            throw new Exception('Commit null');

        if (!$branch->getOwner())
            throw new Exception('Owner null');

        if (empty($branch->getUrl()))
            $branch->setUrl(self::convertToUrl($branch->getCommit()->getNom()));

        if (empty($branch->getUrl()))
            throw new Exception($branch->getCommit()->getNom());

        if (count($this->pageEleveurRepository->findBy(['url' => $branch->getUrl()])) > 0)
            throw new PageEleveurException('Une page eleveur du meme nom existe deja');

        if (count($this->pageEleveurRepository->findBy(['owner' => $branch->getOwner()])) > 0)
            throw new PageEleveurException('Vous avez deja une page eleveur');

        $branch->getOwner()->addRole(ERole::ELEVEUR);

        $this->doctrine->persist($branch);
        $this->doctrine->persist($branch->getCommit());

        $this->doctrine->flush();

        return $branch;
    }


    /**
     * @param $str string
     * @return string
     */
    public static function convertToUrl($str)
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
        return trim($ascii, '-');
    }

    /**
     * @param $pageEleveurId int
     * @param PageEleveurCommit $commit
     * @param User $user
     * @throws PageEleveurException
     */
    public function commit($pageEleveurId, PageEleveurCommit $commit, User $user)
    {
        /**
         * @var PageEleveur $pageEleveur
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