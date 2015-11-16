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
     * @var EntityRepository $pageEleveurRepository $pageEleveurReflogRepository
     */
    private $pageEleveurRepository, $pageEleveurReflogRepository;

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

        $this->pageEleveurReflogRepository = $this
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
                ['AppBundle:PageEleveur',$this->pageEleveurRepository],
                [ 'AppBundle:PageEleveurReflog', $this->pageEleveurReflogRepository]]));

        $this->logger = $this->getMockBuilder('Symfony\Bridge\Monolog\Logger')->disableOriginalConstructor()->getMock();
        $this->pageEleveurService = new PageEleveurService($this->entityManager, $this->logger);
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
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        $commit1 = $this->newCommit(1);
        $pageEleveur->setCommit($commit1);

        $reflog1 = $this->getMockBuilder('\AppBundle\Entity\PageEleveurReflog')
            ->disableOriginalConstructor()->getMock();
        $reflog1->method('getCommit')->willReturn($commit1);
        $this->pageEleveurReflogRepository->method('findBy')->willReturn([$reflog1]);

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
        $pageEleveur = new PageEleveur();
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

    public function testReflogManquant()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        $commit1 = $this->newCommit(1);
        $pageEleveur->setCommit($commit1);

        $this->pageEleveurReflogRepository->method('findBy')->willReturn([]);

        $commit2= $this->newCommit(2, $commit1);

        $this->logger->expects($this->once())->method('error');
        $this->pageEleveurService->commit('', $commit2, $user);
    }

    public function testReflogIncoherent()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository->expects($this->any())
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        $commit1 = $this->newCommit(1);

        // le reflog est resté sur commit1
        $reflog1 = $this->getMockBuilder('\AppBundle\Entity\PageEleveurReflog')
            ->disableOriginalConstructor()->getMock();
        $reflog1->method('getCommit')->willReturn($commit1);
        $this->pageEleveurReflogRepository->method('findBy')->willReturn([$reflog1]);

        // la page est sur commit2
        $commit2= $this->newCommit(2, $commit1);
        $pageEleveur->setCommit($commit2);

        //on essaye de commiter commit3 descendant de commit2
        $commit3= $this->newCommit(3, $commit2);

        // le reflog n'est pas là où il devrait être (commit2) mais le commit passe quand meme
        $this->logger->expects($this->once())->method('error');
        $this->pageEleveurService->commit('', $commit3, $user);
    }
}