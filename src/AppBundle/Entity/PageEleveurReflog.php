<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 05/11/15
 * Time: 21:51
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur_reflog")
 */
class PageEleveurReflog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PageEleveur")
     * @var PageEleveur
     */
    private $pageEleveur;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $logEntry;

    /**
     * @ORM\Column(length=120)
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(length=200)
     * @var string
     */
    private $commentaire;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PageEleveur
     */
    public function getPageEleveur()
    {
        return $this->pageEleveur;
    }

    public function setPageEleveur(PageEleveur $pageEleveur)
    {
        $this->pageEleveur = $pageEleveur;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return int
     */
    public function getLogEntry()
    {
        return $this->logEntry;
    }

    /**
     * @param  int $logEntry
     */
    public function setLogEntry($logEntry)
    {
        $this->logEntry = $logEntry;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * @param string $commentaire
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;
    }
}