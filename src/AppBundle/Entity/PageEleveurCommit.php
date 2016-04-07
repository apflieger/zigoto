<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 19/01/2016
 * Time: 23:43
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur_commit")
 */
class PageEleveurCommit implements PersistableInterface
{
    use Persistable;

    /**
     * @ORM\OneToOne(targetEntity="PageEleveurCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @var PageEleveurCommit
     **/
    private $parent;

    /**
     * @ORM\Column(type="string", length=120)
     * @var string
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @var string
     */
    private $especes;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @var string
     */
    private $races;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     * @var string
     */
    private $lieu;

    /**
     * @ORM\ManyToMany(targetEntity="PageAnimalBranch")
     * @ORM\JoinTable(name="page_eleveur_commit_page_animal",
     *      joinColumns={@ORM\JoinColumn(name="page_eleveur_commit_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="page_animal_id", referencedColumnName="id")}
     *      )
     * @var ArrayCollection
     */
    private $animaux;

    /**
     * @param PageEleveurCommit|null $parent
     * @param string $nom
     * @param string $description
     * @param string $especes
     * @param string $races
     * @param string $lieu
     * @param PageAnimalBranch[]|null $animaux
     */
    public function __construct(PageEleveurCommit $parent = null, $nom, $description, $especes, $races, $lieu, $animaux)
    {
        $this->parent = $parent;
        $this->nom = $nom;
        $this->description = $description;
        $this->especes = $especes;
        $this->races = $races;
        $this->lieu = $lieu;
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

    /**
     * @return ArrayCollection
     */
    public function getAnimaux()
    {
        return $this->animaux;
    }

    /**
     * @return string
     */
    public function getEspeces()
    {
        return $this->especes;
    }

    /**
     * @return string
     */
    public function getRaces()
    {
        return $this->races;
    }

    public function getLieu()
    {
        return $this->lieu;
    }
}