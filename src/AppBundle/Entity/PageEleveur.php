<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 06/11/15
 * Time: 17:05
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur")
 */
class PageEleveur
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $url;

    /**
     * Il ne peut y avoir qu'une page eleveur par utilisateur
     * @ORM\OneToOne(targetEntity="User")
     * @var User
     */
    private $owner;

    /**
     * @ORM\OneToOne(targetEntity="PageEleveurCommit")
     * @var PageEleveurCommit
     */
    private $commit;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return PageEleveurCommit
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param PageEleveurCommit $commit
     */
    public function setCommit(PageEleveurCommit $commit)
    {
        $this->commit = $commit;
    }

    public function getNom()
    {
        return $this->getCommit()->getNom();
    }

    public function getDescription()
    {
        return $this->getCommit()->getDescription();
    }
}