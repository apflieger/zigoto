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
 * @ORM\Table(name="fos_user")
 */
class User extends \FOS\UserBundle\Entity\User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->addRole('ROLE_ELEVEUR');
    }
}