<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 04/01/2016
 * Time: 23:24
 */

namespace AppBundle\Service;


use AppBundle\Entity\Actualite;
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
use InvalidArgumentException;
use Symfony\Bridge\Monolog\Logger;

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

    /** @var Logger */
    private $logger;

    public function __construct(
        EntityManager $doctrine,
        PageEleveurBranchRepository $pageEleveurBranchRepository,
        PageAnimalBranchRepository $pageAnimalBranchRepository,
        EntityRepository $pageEleveurCommitRepository,
        Logger $logger
    ) {
        $this->doctrine = $doctrine;
        $this->pageEleveurBranchRepository = $pageEleveurBranchRepository;
        $this->pageAnimalBranchRepository = $pageAnimalBranchRepository;
        $this->pageEleveurCommitRepository = $pageEleveurCommitRepository;
        $this->logger = $logger;
    }

    /**
     * @param PageEleveurBranch|null $branch
     * @return PageEleveur
     */
    private function fromBranch(PageEleveurBranch $branch = null)
    {
        $pageEleveur = new PageEleveur();
        $pageEleveur->setId($branch->getId());
        $pageEleveur->setOwner($branch->getOwner());
        $pageEleveur->setSlug($branch->getSlug());
        $pageEleveur->setHead($branch->getCommit()->getId());
        $pageEleveur->setNom($branch->getCommit()->getNom());
        $pageEleveur->setDescription($branch->getCommit()->getDescription());
        $pageEleveur->setEspeces($branch->getCommit()->getEspeces());
        $pageEleveur->setRaces($branch->getCommit()->getRaces());
        $pageEleveur->setLieu($branch->getCommit()->getLieu());

        /*
         * Le tri permet de retourner une PE dans le bon ordre même si
         * le commit a été fait dans un mauvais ordre.
         */
        $arrayCollection = $branch->getCommit()->getAnimaux()->toArray();
        usort($arrayCollection, function(PageAnimalBranch $a, PageAnimalBranch $b){
            return $b->getCreatedAt()->getTimestamp() - $a->getCreatedAt()->getTimestamp();
        });

        $animaux = [];
        /** @var PageAnimalBranch $pageAnimalBranch */
        foreach ($arrayCollection as $pageAnimalBranch) {
            $animaux[] = PageAnimalService::fromBranch($pageAnimalBranch);
        }
        $pageEleveur->setAnimaux($animaux);

        $actualites = $branch->getCommit()->getActualites()->toArray();
        usort($actualites, function(Actualite $a, Actualite $b){
            return $b->getDate()->getTimestamp() - $a->getDate()->getTimestamp();
        });

        $pageEleveur->setActualites($actualites);

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
     * @throws HistoryException
     */
    public function create($nom, User $owner)
    {
        if ($this->pageEleveurBranchRepository->findByOwner($owner)) {
            $this->logger->notice('', debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            throw new HistoryException(HistoryException::DEJA_OWNER);
        }

        $commit = new PageEleveurCommit(null, $nom, null, null, null, null, null, null);
        $pageEleveurBranch = new PageEleveurBranch();
        $pageEleveurBranch->setCommit($commit);
        try {
            $pageEleveurBranch->setSlug(static::slug($nom));
        } catch (InvalidArgumentException $e) {
            throw new HistoryException(HistoryException::NOM_INVALIDE);
        }
        $pageEleveurBranch->setOwner($owner);

        if ($this->pageEleveurBranchRepository->findBySlug($pageEleveurBranch->getSlug()))
            throw new HistoryException(HistoryException::SLUG_DEJA_EXISTANT);

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

        $managedActialites = [];
        if ($commitingPageEleveur->getActualites() !== null) {
            foreach ($commitingPageEleveur->getActualites() as $actualite) {
                if ($actualite->hashCode() !== $actualite->getId()) {
                    // l'actualité a été modifiée, on doit persister la nouvelle version
                    $this->doctrine->persist($actualite);
                } else {
                    // L'actualité n'a pas changé
                    $actualite = $this->doctrine->merge($actualite);
                }
                array_push($managedActialites, $actualite);
            }
        }

        $newCommit = new PageEleveurCommit(
            $clientHead,
            $commitingPageEleveur->getNom(),
            $commitingPageEleveur->getDescription(),
            $commitingPageEleveur->getEspeces(),
            $commitingPageEleveur->getRaces(),
            $commitingPageEleveur->getLieu(),
            $commitingPageEleveur->getAnimaux() !== null ?
                array_map(
                    function(PageAnimal $pageAnimal) use ($user) {
                        if ($pageAnimal->getOwner()->getId() !== $user->getId())
                            throw new HistoryException(HistoryException::DROIT_REFUSE);
                        return $this->pageAnimalBranchRepository->find($pageAnimal->getId());
                    }, $commitingPageEleveur->getAnimaux()
                ) :
                [],
            $managedActialites
        );

        $this->doctrine->persist($newCommit);
        $pageEleveurBranch->setCommit($newCommit);
        $this->doctrine->flush([$newCommit, $pageEleveurBranch]);

        $pageEleveur = $this->fromBranch(
            $this->findAtCommit(
                $commitingPageEleveur->getId(),
                $newCommit->getId()
            )
        );

        return $pageEleveur;
    }

    /**
     * @param string $pageEleveurId
     * @param string|null $commitId
     * @return PageEleveurBranch
     * @throws HistoryException
     */
    private function findAtCommit($pageEleveurId, $commitId = null)
    {
        /** @var PageEleveurBranch $branch */
        $branch = $this->pageEleveurBranchRepository->find($pageEleveurId);

        if (!empty($commitId)){
            /** @var PageEleveurCommit $commit */
            $commit = $this->pageEleveurCommitRepository->find($commitId);

            $branch->setCommit($commit);
        }

        return $branch;
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
            throw new InvalidArgumentException($str);

        return $ascii;
    }

    /**
     * @param PageAnimal $pageAnimal
     * @return PageEleveur
     */
    public function findByPageAnimal(PageAnimal $pageAnimal)
    {
        $pageAnimalBranch = $this->pageEleveurBranchRepository->findByPageAnimal($pageAnimal);

        if ($pageAnimalBranch === null)
            return null;

        return $this->fromBranch($pageAnimalBranch);
    }
}