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

class PageEleveurService
{

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $nomPageEleveur string
     * @param $owner User
     * @return PageEleveur
     * @throws \Exception
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

        $pageEleveurCommit = new PageEleveurCommit();
        $pageEleveurCommit->setNom($nomPageEleveur);

        $pageEleveur->setCommit($pageEleveurCommit);

        //Ajout de la 1ere entrée dans le reflog de cette page
        $reflog = new PageEleveurReflog();
        $reflog->setUrl($pageEleveur->getUrl());
        $reflog->setDateTime(new \DateTime());
        $reflog->setLogEntry(0);
        $reflog->setPageEleveur($pageEleveur);
        $reflog->setUser($owner);
        $reflog->setCommentaire("Création de la page");

        $this->doctrine->getManager()->persist($pageEleveurCommit);
        $this->doctrine->getManager()->persist($pageEleveur);
        $this->doctrine->getManager()->persist($reflog);

        $this->doctrine->getManager()->flush();

        return $pageEleveur;
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
     * @return PageEleveur
     */
    public function getForUrl($url)
    {
        /**
         * @var PageEleveur $pageEleveur
         */
        $pageEleveur = $this->doctrine->getRepository('AppBundle:PageEleveur')->findOneBy(['url' => $url]);

        return $pageEleveur;
    }

}