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
 * @ORM\Table(name="user")
 *
 */
class User extends \FOS\UserBundle\Entity\User implements Identifiable
{
    use HasId;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16)
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }
}