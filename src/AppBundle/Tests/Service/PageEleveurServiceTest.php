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
use AppBundle\Service\HistoryException;
use AppBundle\Service\HistoryService;
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

    /**
     * @var HistoryService $historyService
     */
    private $historyService;

    /**
     * @var EntityRepository
     */
    private $pageEleveurCommitRepository;


    /**
     * @before
     */
    public function setup()
    {
        static::bootKernel(array());

        $this->pageEleveurRepository = $this
            ->getMockBuilder('AppBundle\Repository\PageEleveurRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageEleveurCommitRepository = $this
            ->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->method('getRepository')
            ->will($this->returnValueMap([
                ['AppBundle:PageEleveur',$this->pageEleveurRepository]]));

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
            ->method('findBy')
            ->willReturn(array(), new PageEleveur(null, $user));
        $this->pageEleveurService->create('page2', $user);
    }

    /**
     * @expectedException \Exception
     */
    public function testDeuxUserMemePage()
    {
        $user = new User();
        $this->pageEleveurRepository
            ->method('findBy')
            ->willReturn(new PageEleveur(null, $user));

        $this->pageEleveurService->create('page2', $user);
    }

    public function testConvertionUrl()
    {
        // conservation des caractères de base
        $this->assertEquals('azertyuiopqsdfghjklmwxcvbn1234567890', PageEleveurService::slug('azertyuiopqsdfghjklmwxcvbn1234567890'));

        // trim
        $this->assertEquals('aaa', PageEleveurService::slug(' aaa '));

        // to lowercase
        $this->assertEquals('aaa', PageEleveurService::slug('AaA'));

        // suppression des caractères spéciaux
        $this->assertEquals('', PageEleveurService::slug('!?,.<>=&'));

        // remplacement des caractères convertibles
        $this->assertEquals('eureace', PageEleveurService::slug('€éàçè&'));

        // espaces convertis en dash
        $this->assertEquals('un-deux-trois', PageEleveurService::slug('un deux trois'));
    }
}