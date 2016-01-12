<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 22:11
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageAnimalRepository")
 * @ORM\Table(name="page_animal")
 */
class PageAnimal implements BranchInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, unique=true)
     * @var string
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var User
     */
    private $owner;

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return null
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

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
        // TODO: Implement getCommit() method.
    }

    /**
     * @param CommitInterface $commit
     * @return null
     */
    public function setCommit(CommitInterface $commit)
    {
        // TODO: Implement setCommit() method.
    }
}