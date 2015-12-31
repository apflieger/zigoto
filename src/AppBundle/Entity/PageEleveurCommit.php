<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 08/11/15
 * Time: 17:24
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_eleveur_commit")
 */
class PageEleveurCommit implements CommitInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToOne(targetEntity="PageEleveurCommit")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    private $parent;

    /**
     * @param string $nom
     * @param string $description
     * @param PageEleveurCommit|null $parent
     */
    public function __construct($nom, $description, PageEleveurCommit $parent = null)
    {
        $this->nom = $nom;
        $this->description = $description;
        $this->parent = $parent;
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
}