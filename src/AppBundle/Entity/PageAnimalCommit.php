<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 13/01/2016
 * Time: 22:57
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_animal_commit")
 *
 * @ExclusionPolicy("all")
 */
class PageAnimalCommit implements CommitInterface
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
     * @ORM\OneToOne(targetEntity="PageAnimalCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @var PageAnimalCommit
     * @Type("AppBundle\Entity\PageAnimalCommit")
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=120)
     * @var string
     *
     * @Expose
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