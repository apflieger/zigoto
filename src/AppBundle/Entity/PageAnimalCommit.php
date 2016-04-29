<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_animal_commit")
 */
class PageAnimalCommit implements IdentityPersistableInterface
{
    use Persistable;

    /**
     * @ORM\OneToOne(targetEntity="PageAnimalCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * @var PageAnimalCommit
     **/
    private $parent;

    /**
     * @ORM\Column(type="string", length=120)
     * @var string
     */
    private $nom;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateNaissance;

    /**
     * @var string
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $statut;

    /**
     * @var Photo[]
     * @ORM\Column(type="array", nullable=true)
     */
    private $photos;

    /**
     * PageAnimalCommit constructor.
     * @param PageAnimalCommit|null $parent
     * @param string $nom
     * @param DateTime $dateNaissance
     * @param string $description
     * @param int $statut
     * @param Photo[] $photos
     */
    public function __construct(
        PageAnimalCommit $parent = null,
        $nom,
        DateTime $dateNaissance = null,
        $description,
        $statut,
        $photos
    ) {
        $this->parent = $parent;
        $this->nom = $nom;
        $this->dateNaissance = $dateNaissance;
        $this->description = $description;
        $this->statut = $statut;
        $this->photos = $photos;
    }

    /**
     * @return PageAnimalCommit
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
     * @return DateTime
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * @return Photo[]
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}