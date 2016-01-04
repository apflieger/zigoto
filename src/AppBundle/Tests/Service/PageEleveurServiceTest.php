<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 14/11/15
 * Time: 16:13
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Service\PageEleveurException;
use AppBundle\Service\PageEleveurService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PageEleveurServiceTest extends KernelTestCase
{
    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var PageEleveurService $pageEleveurService
     */
    private $pageEleveurService;

    /**
     * @var EntityRepository $pageEleveurRepository
     */
    private $pageEleveurRepository;

    private $logger;

    /**
     * @before
     */
    public function setup()
    {
        static::bootKernel(array());

        $this->pageEleveurRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                ['AppBundle:PageEleveur',$this->pageEleveurRepository]]));

        $this->logger = $this->getMockBuilder('Symfony\Bridge\Monolog\Logger')->disableOriginalConstructor()->getMock();
        $this->pageEleveurService = new PageEleveurService($this->entityManager, $this->pageEleveurRepository, $this->logger);
    }

    /**
     * @expectedException \Exception
     */
    public function testUrlVide()
    {
        $this->pageEleveurService->create(new PageEleveur(new PageEleveurCommit('', '', null), new User()), new User());
    }

    /**
     * @expectedException \AppBundle\Service\PageEleveurException
     */
    public function testPageInexistante()
    {
        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn(null);

        $this->pageEleveurService->commit('', new PageEleveurCommit('', '', null), new User());
    }

    private function newCommit($id, $parent = null)
    {
        $commit= $this->getMockBuilder('\AppBundle\Entity\PageEleveurCommit')
            ->disableOriginalConstructor()->getMock();
        $commit->method('getId')->willReturn($id);
        $commit->method('getParent')->willReturn($parent);
        return $commit;
    }

    public function testCommitFastForward()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveur = new PageEleveur(null, $user);
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        $commit1 = $this->newCommit(1);
        $pageEleveur->setCommit($commit1);

        $commit2 = $this->newCommit(2, $commit1);

        $this->logger->expects($this->never())->method('error');
        $this->pageEleveurService->commit('', $commit2, $user);
    }

    /**
     * @expectedException \AppBundle\Service\PageEleveurException
     */
    public function testCommitNonFastForward()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveur = new PageEleveur(null, $user);
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        // commit1 est l'avant dernier etat de la page
        $commit1 = $this->newCommit(1);

        // commit2 est l'état courant de la page, il descend de commit1
        $commit2 = $this->newCommit(2, $commit1);
        $pageEleveur->setCommit($commit2);

        // commit3 descend de commit1
        $commit3 = $this->newCommit(3, $commit1);

        // le commit sur commit3 doit échouer car il n'est pas fastforward depuis commit2
        $this->pageEleveurService->commit('', $commit3, $user);
    }

    /**
     * @expectedException \AppBundle\Service\PageEleveurException
     */
    public function testUnUserDeuxPages()
    {
        $user = new User();
        $this->pageEleveurRepository
            ->method('findBy')
            ->willReturn(array(), new PageEleveur(null, $user));
        $this->pageEleveurService->create(new PageEleveur(new PageEleveurCommit('page2', '', null), $user), $user);
    }

    /**
     * @expectedException \AppBundle\Service\PageEleveurException
     */
    public function testDeuxUserMemePage()
    {
        $user = new User();
        $this->pageEleveurRepository
            ->method('findBy')
            ->willReturn(new PageEleveur(null, $user));

        $this->pageEleveurService->create(new PageEleveur(new PageEleveurCommit('page2', '', null), $user), $user);
    }

    public function testConvertionUrl()
    {
        // conservation des caractères de base
        $this->assertEquals('azertyuiopqsdfghjklmwxcvbn1234567890', PageEleveurService::convertToUrl('azertyuiopqsdfghjklmwxcvbn1234567890'));

        // trim
        $this->assertEquals('aaa', PageEleveurService::convertToUrl(' aaa '));

        // to lowercase
        $this->assertEquals('aaa', PageEleveurService::convertToUrl('AaA'));

        // suppression des caractères spéciaux
        $this->assertEquals('', PageEleveurService::convertToUrl('!?,.<>=&'));

        // remplacement des caractères convertibles
        $this->assertEquals('eureace', PageEleveurService::convertToUrl('€éàçè&'));

        // espaces convertis en dash
        $this->assertEquals('un-deux-trois', PageEleveurService::convertToUrl('un deux trois'));
    }
}