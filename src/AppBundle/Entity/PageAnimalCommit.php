<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 22:57
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_animal_commit")
 */
class PageAnimalCommit implements CommitInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="PageAnimalCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @var PageAnimalCommit
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=120)
     * @var string
     */
    private $nom;

    public function __construct(PageAnimalCommit $parent = null, $nom)
    {
        $this->parent = $parent;
        $this->nom = $nom;
    }

    /**
     * @return CommitInterface | null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}