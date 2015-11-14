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
     * @ORM\ManyToOne(targetEntity="PageEleveurCommit")
     * @ORM\JoinColumn(nullable=false)
     * @var PageEleveurCommit
     */
    private $commit;

    /**
     * @param PageEleveur $pageEleveur
     * @param User $user
     * @param \DateTime $dateTime
     * @param int $logEntry
     * @param string $url
     * @param string $commentaire
     */
    public function __construct(PageEleveur $pageEleveur, User $user, \DateTime $dateTime, $logEntry, $url, $commentaire, PageEleveurCommit $commit)
    {
        $this->pageEleveur = $pageEleveur;
        $this->user = $user;
        $this->dateTime = $dateTime;
        $this->logEntry = $logEntry;
        $this->url = $url;
        $this->commentaire = $commentaire;
        $this->commit = $commit;
    }

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

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @return int
     */
    public function getLogEntry()
    {
        return $this->logEntry;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * @return PageEleveurCommit
     */
    public function getCommit()
    {
        return $this->commit;
    }

}