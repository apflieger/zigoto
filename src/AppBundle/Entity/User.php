<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 28/10/15
 * Time: 13:18
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 */
class User extends \FOS\UserBundle\Entity\User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @Type("string")
     */
    protected $id;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function __construct()
    {
        parent::__construct();
    }
}