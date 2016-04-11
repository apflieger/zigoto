<?php
/**
 * Created by PhpStorm.
 * User: apflieger
 * Date: 28/02/16
 * Time: 21:11
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\IdentityPersistableInterface;
use AppBundle\Tests\TestTimeService;
use DateInterval;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PersistableTest extends KernelTestCase
{
    /** @var UserManager */
    private $userManager;

    /** @var TestTimeService */
    private $timeService;

    public function setup()
    {
        self::bootKernel();
        $this->userManager = self::$kernel->getContainer()->get('fos_user.user_manager');

        $this->timeService = self::$kernel->getContainer()->get('zigotoo.time');
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

        $this->assertTrue($user instanceof IdentityPersistableInterface);

        $this->assertNull($user->getId());
        $this->assertNull($user->getCreatedAt());

        $this->timeService->lockNow();
        $this->userManager->updateUser($user);

        $this->assertEquals(16, strlen($user->getId()));

        $this->assertEquals($this->timeService->now(), $user->getCreatedAt());
    }

    public function testModifiedAt()
    {
        $user = $this->createUser();

        $this->timeService->lockNow();
        $this->userManager->updateUser($user);

        // A la création de l'entité, createdAt et modifiedAt ont la même valeur, 'now'
        $this->assertEquals($this->timeService->now(), $user->getModifiedAt());

        //modification du user
        $user->setPlainPassword('modified');

        //enregistrement du user une seconde plus tard
        $this->timeService->lockNow($this->timeService->now()->add(new DateInterval('PT1S'))); // Simule un sleep(1)
        $this->userManager->updateUser($user);


        $this->assertEquals($this->timeService->now(), $user->getModifiedAt());
        $this->assertGreaterThan($user->getCreatedAt(), $user->getModifiedAt());
    }

    /**
     * @return UserInterface|IdentityPersistableInterface
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