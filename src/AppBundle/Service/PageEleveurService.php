<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 12/11/15
 * Time: 15:10
 */

namespace AppBundle\Service;


use AppBundle\Entity\ERole;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\PageEleveurReflog;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;

class PageEleveurService
{

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, Logger $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    /**
     * @param string $nomPageEleveur
     * @param User $owner
     * @return string
     * @throws PageEleveurException
     */
    public function create($nomPageEleveur, User $owner)
    {
        $pageEleveurRepository = $this->doctrine->getRepository('AppBundle:PageEleveur');

        $urlPageEleveur = self::convertToUrl($nomPageEleveur);

        if (count($pageEleveurRepository->findBy(['url' => $urlPageEleveur])) > 0)
            throw new PageEleveurException('Une page eleveur du meme nom existe deja');

        if (count($pageEleveurRepository->findBy(['owner' => $owner])) > 0)
            throw new PageEleveurException('Vous avez deja une page eleveur');

        $owner->addRole(ERole::ELEVEUR);

        //Création de la page eleveur
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($owner);
        $pageEleveur->setUrl($urlPageEleveur);

        $pageEleveurCommit = new PageEleveurCommit($nomPageEleveur, '', NULL);

        $pageEleveur->setCommit($pageEleveurCommit);

        //Ajout de la 1ere entrée dans le reflog de cette page
        $reflog = new PageEleveurReflog(
            $pageEleveur,
            $owner,
            new \DateTime(),
            0,
            $pageEleveur->getUrl(),
            'create',
            $pageEleveurCommit);

        $this->doctrine->getManager()->persist($pageEleveurCommit);
        $this->doctrine->getManager()->persist($pageEleveur);
        $this->doctrine->getManager()->persist($reflog);

        $this->doctrine->getManager()->flush();

        return $pageEleveur->getUrl();
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
        $ascii = preg_replace("/[^a-zA-Z0-9\/_+ -]/", '', $ascii);

        // lowercase
        $ascii = strtolower($ascii);

        // remplacement de tout ce qui n'est pas chiffres ou lettres par le séparateur '-'
        $ascii = preg_replace("/[\/_+ -]+/", '-', $ascii);

        // trim
        return trim($ascii, '-');
    }

    /**
     * @param $url string
     * @return PageEleveurCommit
     */
    public function getCommitByUrl($url)
    {
        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $this->doctrine->getRepository('AppBundle:PageEleveur')->findOneBy(['url' => $url]);

        if (is_null($pageEleveur))
            return null;
        else
            return $pageEleveur->getCommit();
    }

    /**
     * @param int $id
     * @return PageEleveurCommit
     */
    public function getCommit($id)
    {
        /**
         * @var PageEleveurCommit $pageEleveurCommit
         */
        $pageEleveurCommit = $this->doctrine->getRepository('AppBundle:PageEleveurCommit')->find($id);
        return $pageEleveurCommit;
    }

    /**
     * @param string $url
     * @param PageEleveurCommit $commit
     * @param User $user
     * @throws PageEleveurException
     */
    public function commit($url, PageEleveurCommit $commit, User $user)
    {
        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $this->doctrine->getRepository('AppBundle:PageEleveur')->findOneBy(['url' => $url, 'owner' => $user]);

        if (!$pageEleveur)
            throw new PageEleveurException();

        if ($pageEleveur->getCommit()->getId() !== $commit->getParent()->getId())
            throw new PageEleveurException();

        $this->doctrine->getManager()->persist($commit);
        $pageEleveur->setCommit($commit);

        /**
         * @var PageEleveurReflog $headReflog
         */
        $headReflog = $this->doctrine->getRepository('AppBundle:PageEleveurReflog')->findBy(
            ['pageEleveur' => $pageEleveur],
            ['logEntry' => 'DESC'],
            1)[0];

        $reflogMessage = 'error on commit';
        $reflogEntry = -1;

        if (!$headReflog)
        {
            $this->logger->error('Pas d\' entrée au reflog de ' . $url . ' - page eleveur ' . $pageEleveur->getId());
        }
        else if ($headReflog->getCommit()->getId() !== $commit->getParent()->getId())
        {
            $this->logger->error('Incohérence dans le reflog de ' . $url . ' headReflog : ' . $headReflog->getId() .
            ' n\'est pas sur le commit ' . $commit->getParent()->getId());
        }
        else
        {
            $reflogMessage = 'commit';
            $reflogEntry = $headReflog->getLogEntry() +1;
        }

        $reflog = new PageEleveurReflog(
            $pageEleveur,
            $user,
            new \DateTime(),
            $reflogEntry,
            $pageEleveur->getUrl(),
            $reflogMessage,
            $commit);

        $this->doctrine->getManager()->persist($reflog);

        $this->doctrine->getManager()->flush();
    }
}