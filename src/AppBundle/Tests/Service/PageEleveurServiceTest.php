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
use AppBundle\Repository\PageEleveurRepository;
use AppBundle\Service\HistoryService;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit_Framework_TestCase;

class PageEleveurServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var PageEleveurRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageEleveurRepository;

    /**
     * @var HistoryService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyService;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageEleveurCommitRepository;

    /**
     * @var PageEleveurService
     */
    private $pageEleveurService;

    public function setup()
    {
        $this->pageEleveurRepository = $this
            ->getMockBuilder(PageEleveurRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageEleveurCommitRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->historyService = new HistoryService($this->entityManager, $this->pageEleveurRepository);
        $this->pageEleveurService = new PageEleveurService(
            $this->historyService,
            $this->pageEleveurRepository,
            $this->pageEleveurCommitRepository);
    }

    /**
     * @expectedException \Exception
     */
    public function testUrlVide()
    {
        $this->pageEleveurService->create('', new User());
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     */
    public function testPageInexistante()
    {
        $this->pageEleveurRepository
            ->method('find')->withAnyParameters()->willReturn(null);

        $this->pageEleveurService->commit(new User(), 0, 0, '','');
    }

    /**
     * @param $id
     * @param null $parent
     * @return PageEleveurCommit|\PHPUnit_Framework_MockObject_MockObject
     */
    private function newCommit($id, $parent = null)
    {
        $commit= $this->getMockBuilder(PageEleveurCommit::class)
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
        $pageEleveur->setId(1);
        $pageEleveur->setOwner($user);

        $this->pageEleveurRepository
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        $commit1 = $this->newCommit(1);

        $this->pageEleveurCommitRepository
            ->method('find')->withAnyParameters()->willReturn($commit1);

        $pageEleveur->setCommit($commit1);

        $this->pageEleveurService->commit(
            $user,
            $pageEleveur->getId(),
            $commit1->getId(),
            '',
            '');

        // pas d'exception
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     */
    public function testCommitNonFastForward()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveur = new PageEleveur();
        $pageEleveur->setOwner($user);
        $pageEleveur->setId(1);

        $this->pageEleveurRepository
            ->method('find')->withAnyParameters()->willReturn($pageEleveur);

        // commit1 est l'avant dernier etat de la page
        $commit1 = $this->newCommit(1);

        // commit2 est l'état courant de la page, il descend de commit1
        $commit2 = $this->newCommit(2, $commit1);
        $pageEleveur->setCommit($commit2);

        $this->pageEleveurCommitRepository
            ->method('find')->willReturn($commit1);

        // le commit sur commit3 doit échouer car il n'est pas fastforward depuis commit2
        $this->pageEleveurService->commit($user, $pageEleveur->getId(), $commit1->getId(), '', '');
    }

    /**
     * @expectedException \Exception
     */
    public function testUnUserDeuxPages()
    {
        $user = new User();
        $this->pageEleveurRepository
            ->method('findByOwner')
            ->willReturn(new PageEleveur(null, $user));
        $this->pageEleveurService->create('page2', $user);
    }

    /**
     * @expectedException \Exception
     */
    public function testDeuxUserMemePage()
    {
        $user = new User();
        $this->pageEleveurRepository
            ->method('findBySlug')
            ->willReturn(new PageEleveur(null, $user));

        $this->pageEleveurService->create('page2', $user);
    }
}