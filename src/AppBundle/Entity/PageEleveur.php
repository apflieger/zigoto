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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageEleveurRepository")
 * @ORM\Table(name="page_eleveur")
 */
class PageEleveur implements BranchInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, unique=true)
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
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @inheritdoc
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @inheritdoc
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @inheritdoc
     */
    public function setCommit(CommitInterface $commit)
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
