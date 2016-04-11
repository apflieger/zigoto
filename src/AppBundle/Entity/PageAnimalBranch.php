<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:46
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageAnimalBranchRepository")
 * @ORM\Table(name="page_animal")
 */
class PageAnimalBranch implements IdentityPersistableInterface
{
    use Persistable;

    /**
     * @ORM\OneToOne(targetEntity="PageAnimalCommit")
     *
     * @var PageAnimalCommit
     */
    private $commit;

    /**
     *
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $owner;

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return PageAnimalCommit
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param PageAnimalCommit $commit
     */
    public function setCommit(PageAnimalCommit $commit)
    {
        $this->commit = $commit;
    }
}