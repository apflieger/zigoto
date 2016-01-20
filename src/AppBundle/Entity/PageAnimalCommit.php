<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 20/01/2016
 * Time: 00:43
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_animal_commit")
 */
class PageAnimalCommit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    private $id;

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
    protected $nom;

    /**
     * PageAnimalCommit constructor.
     * @param PageAnimalCommit|null $parent
     * @param string $nom
     */
    public function __construct(PageAnimalCommit $parent = null, $nom)
    {
        $this->nom = $nom;
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }
}