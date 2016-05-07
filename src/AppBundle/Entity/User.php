<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
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