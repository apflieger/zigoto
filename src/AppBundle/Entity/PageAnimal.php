<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 22:11
 */

namespace AppBundle\Entity;


class PageAnimal implements BranchInterface
{
    /** @var string */
    private $slug;

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
        // TODO: Implement getOwner() method.
    }

    /**
     * @param User $owner
     * @return null
     */
    public function setOwner(User $owner)
    {
        // TODO: Implement setOwner() method.
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