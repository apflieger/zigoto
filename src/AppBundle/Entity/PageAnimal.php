<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 22:11
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageAnimalRepository")
 * @ORM\Table(name="page_animal")
 *
 * @ExclusionPolicy("all")
 */
class PageAnimal implements BranchInterface
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
     * @ORM\ManyToOne(targetEntity="User")
     * @var User
     * @Type("AppBundle\Entity\User")
     */
    private $owner;

    /**
     * @ORM\OneToOne(targetEntity="PageAnimalCommit")
     * @var PageAnimalCommit
     * @Type("AppBundle\Entity\PageAnimalCommit")
     *
     * @Expose
     */
    private $commit;

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     * @return null
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return CommitInterface
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param CommitInterface $commit
     * @return null
     */
    public function setCommit(CommitInterface $commit)
    {
        $this->commit = $commit;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->commit->getNom();
    }


    public function getHead()
    {
        return $this->commit->getId();
    }
}