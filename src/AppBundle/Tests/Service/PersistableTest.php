<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 21:11
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\PersistableInterface;
use DateTime;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PersistableTest extends KernelTestCase
{
    /** @var UserManager */
    private $userManager;

    public function setup()
    {
        self::bootKernel();
        $this->userManager = self::$kernel->getContainer()->get('fos_user.user_manager');
    }

    public function testPersistableCreation()
    {
        /*
         * Une entité qui utilise le trait Persistable se voit attribué un id hexadecimal
         * de 16 caractères et une date createdAt.
         *
         * On utilise arbitrairement une entité User pour tester le comportement
         */

        $user = $this->createUser();

        $this->assertTrue($user instanceof PersistableInterface);

        $this->assertNull($user->getId());
        $this->assertNull($user->getCreatedAt());

        $this->userManager->updateUser($user);

        $this->assertEquals(16, strlen($user->getId()));

        // Ce test est instable pour le moment
        $this->assertEquals(new DateTime(), $user->getCreatedAt());
    }

    public function testModifiedAt()
    {
        $user = $this->createUser();
        $this->userManager->updateUser($user);

        // Ce test est instable pour le moment
        $this->assertEquals(new DateTime(), $user->getModifiedAt());

        sleep(1);

        $user->setPlainPassword('modified');
        $this->userManager->updateUser($user);

        // Ce test est instable pour le moment
        $this->assertEquals(new DateTime(), $user->getModifiedAt());
    }

    /**
     * @return UserInterface|PersistableInterface
     */
    private function createUser()
    {
        /** @var UserInterface $user */
        $user = $this->userManager->createUser();

        $username = $this->getName() . rand();
        $user->setUsername($username);
        $user->setEmail($username . '@zigotoo.com');
        $user->setPlainPassword('test');
        $user->setEnabled(true);

        return $user;
    }
}