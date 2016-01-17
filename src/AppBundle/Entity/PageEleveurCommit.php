<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 08/11/15
 * Time: 17:24
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur_commit")
 *
 * @ExclusionPolicy("all")
 */
class PageEleveurCommit implements CommitInterface
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
     * @ORM\Column(type="string", length=120)
     * @var string
     *
     * @Expose
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @var string
     *
     * @Expose
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="PageEleveurCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @var PageEleveurCommit
     * @Type("AppBundle\Entity\PageEleveurCommit")
     **/
    private $parent;

    /**
     * @ORM\ManyToMany(targetEntity="PageAnimal")
     * @ORM\JoinTable(name="page_eleveur_commit_page_animal",
     *      joinColumns={@ORM\JoinColumn(name="page_eleveur_commit_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="page_animal_id", referencedColumnName="id")}
     *      )
     * @var ArrayCollection
     *
     * @Expose
     */
    private $animaux;

    /**
     * @param string $nom
     * @param string $description
     * @param PageAnimal[]|null $animaux
     * @param PageEleveurCommit|null $parent
     */
    public function __construct($nom, $description, $animaux = null, PageEleveurCommit $parent = null)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->parent = $parent;
        $this->animaux = new ArrayCollection($animaux ?? []);
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PageEleveurCommit|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return PageAnimal[]
     */
    public function getAnimaux()
    {
        return $this->animaux->toArray();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}