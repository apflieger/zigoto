<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 28/10/15
 * Time: 13:18
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 *
 */
class User extends \FOS\UserBundle\Model\User implements PersistableInterface
{
    use Persistable;

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