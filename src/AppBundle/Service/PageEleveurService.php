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
     * @param $str string
     * @return string
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
            throw new \InvalidArgumentException($str);

        return $ascii;
    }

    /**
     * @param User $user
     * @param int $pageEleveurId
     * @param int $currentCommitId
     * @param string $nom
     * @param string $description
     * @return PageEleveurCommit
     * @throws HistoryException
     */
    public function commit(User $user, $pageEleveurId, $currentCommitId, $nom, $description)
    {
        /** @var PageEleveurCommit $pageEleveur */
        $pageEleveurCommit = $this->pageEleveurCommitRepository->find($currentCommitId);

        $commit = new PageEleveurCommit($nom, $description, $pageEleveurCommit);

        $this->history->commit($pageEleveurId, $commit, $user);

        return $commit;
    }
}