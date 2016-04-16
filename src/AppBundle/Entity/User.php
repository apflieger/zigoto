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
class User extends \FOS\UserBundle\Model\User implements IdentityPersistableInterface
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

    public function __toString()
    {
        return 'id: ' . $this->id . '; userName: ' . $this->getUsername() . '; email: ' . $this->getEmail();
    }
}