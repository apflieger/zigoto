<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 06/11/15
 * Time: 17:05
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageEleveurRepository")
 * @ORM\Table(name="page_eleveur")
 *
 * @ExclusionPolicy("all")
 */

class PageEleveur implements BranchInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, unique=true)
     * @var string
     */
    private $slug;

    /**
     * Il ne peut y avoir qu'une page eleveur par utilisateur
     * @ORM\OneToOne(targetEntity="User")
     * @var User
     * @Type("AppBundle\Entity\User")
     */
    private $owner;

    /**
     * @ORM\OneToOne(targetEntity="PageEleveurCommit")
     * @var PageEleveurCommit
     * @Type("AppBundle\Entity\PageEleveurCommit")
     *
     * @Expose
     */
    private $commit;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
