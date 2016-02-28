<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 00:53
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasId
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16)
     */
    protected $id;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}