<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 27/12/2015
 * Time: 20:44
 */

namespace AppBundle\Entity;


interface BranchInterface
{
    /**
     * @return string
     */
    public function getSlug();

    /**
     * @param string $slug
     * @return null
     */
    public function setSlug($slug);

    /**
     * @return User
     */
    public function getOwner();

    /**
     * @param User $owner
     * @return null
     */
    public function setOwner(User $owner);

    /**
     * @return CommitInterface
     */
    public function getCommit();

    /**
     * @param CommitInterface $commit
     * @return null
     */
    public function setCommit(CommitInterface $commit);
}