<?php
/**
 * Created by PhpStorm.
 * User: apf
 * Date: 14/11/15
 * Time: 16:13
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\Actualite;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\PageEleveurBranch;
use AppBundle\Entity\PageEleveurCommit;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use AppBundle\Repository\PageEleveurBranchRepository;
use AppBundle\Service\PageEleveurService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit_Framework_TestCase;
use Symfony\Bridge\Monolog\Logger;

class PageEleveurServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var PageEleveurBranchRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $pageEleveurBranchRepository;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $pageEleveurCommitRepository;

    /** @var PageEleveurService */
    private $pageEleveurService;

    /** @var PageAnimalBranchRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $pageAnimalBranchRepository;

    public function setup()
    {
        $this->pageEleveurBranchRepository = $this
            ->getMockBuilder(PageEleveurBranchRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageEleveurCommitRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageAnimalBranchRepository = $this
            ->getMockBuilder(PageAnimalBranchRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Logger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageEleveurService = new PageEleveurService(
            $this->entityManager,
            $this->pageEleveurBranchRepository,
            $this->pageAnimalBranchRepository,
            $this->pageEleveurCommitRepository,
            $logger
        );
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::NOM_INVALIDE
     */
    public function testCreate_NomVide()
    {
        $this->pageEleveurService->create('', new User());
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::DEJA_OWNER
     */
    public function testCreate_UnUserDeuxPages()
    {
        $user = new User();
        $pageEleveur1 = new PageEleveur();
        $pageEleveur1->setOwner($user);
        $this->pageEleveurBranchRepository
            ->method('findByOwner')
            ->willReturn($pageEleveur1);
        $this->pageEleveurService->create('page2', $user);
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::SLUG_DEJA_EXISTANT
     */
    public function testCreate_DeuxUserMemePage()
    {
        $user = new User();
        $this->pageEleveurBranchRepository
            ->method('findBySlug')
            ->willReturn(new PageEleveur());

        $this->pageEleveurService->create('page2', $user);
    }

    public function testCreate_Success()
    {
        $user = new User();
        $pageEleveur = $this->pageEleveurService->create('Les Chartreux de Tatouine', $user);

        $this->assertEquals($user, $pageEleveur->getOwner());
        $this->assertEquals('les-chartreux-de-tatouine', $pageEleveur->getSlug());
        $this->assertEquals('Les Chartreux de Tatouine', $pageEleveur->getNom());
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::BRANCHE_INCONNUE
     */
    public function testCommit_PageInexistante()
    {
        $commit1 = $this->newCommit(1);

        $this->pageEleveurCommitRepository
            ->method('find')->withAnyParameters()->willReturn($commit1);

        // cet appel ne sert à rien, c'est juste pour comprendre ce qu'on teste
        $this->pageEleveurBranchRepository
            ->method('find')->withAnyParameters()->willReturn(null);

        $pageEleveur = new PageEleveur();
        $pageEleveur->setId(0);
        $this->pageEleveurService->commit(new User(), $pageEleveur);
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::BRANCHE_INCONNUE
     */
    public function testCommit_ParentInexistant()
    {
        // cet appel ne sert à rien, c'est juste pour comprendre ce qu'on teste
        $this->pageEleveurCommitRepository
            ->method('find')->withAnyParameters()->willReturn(null);

        $pageEleveur = new PageEleveur();
        $pageEleveur->setId(0);

        $this->pageEleveurService->commit(new User(), $pageEleveur);
    }

    /**
     * @param $id
     * @param PageEleveurCommit|null $parent
     * @return PageEleveurCommit|\PHPUnit_Framework_MockObject_MockObject
     */
    private function newCommit($id, PageEleveurCommit $parent = null)
    {
        $commit= $this->getMockBuilder(PageEleveurCommit::class)
            ->disableOriginalConstructor()->getMock();
        $commit->method('getId')->willReturn($id);
        $commit->method('getParent')->willReturn($parent);
        return $commit;
    }

    public function testCommit_FastForward()
    {
        // Mock d'une page eleveur en base de données
        $user = new User();
        $user->setId(1);
        $pageEleveurBranch = new PageEleveurBranch();
        $pageEleveurBranch->setId(1);
        $pageEleveurBranch->setOwner($user);

        $this->pageEleveurBranchRepository
            ->method('find')->withAnyParameters()->willReturn($pageEleveurBranch);

        $commit1 = $this->newCommit(1);

        $pageEleveurBranch->setCommit($commit1);

        $this->pageEleveurCommitRepository
            ->method('find')->withAnyParameters()->willReturn($commit1);

        //Simulation d'une requete de commit
        $pageEleveur = new PageEleveur();
        $pageEleveur->setId($pageEleveurBranch->getId());
        $pageEleveur->setHead($commit1->getId());
        $pageEleveur->setDescription('Une longue description');
        $pageEleveur->setEspeces('Chiens');
        $pageEleveur->setRaces('Chihuahua');
        $pageEleveur->setLieu('Hauts-de-Seine');
        $pageEleveur->setActualites([new Actualite('Nouvelle portée')]);

        $this->entityManager->expects($this->once())->method('flush');
        $this->pageEleveurService->commit($user, $pageEleveur);

        //On vérifie qu'il y a bien un nouveau commit avec les bonnes infos
        $this->assertNotEquals($commit1->getId(), $pageEleveurBranch->getCommit()->getId());
        $this->assertEquals('Une longue description', $pageEleveurBranch->getCommit()->getDescription());
        $this->assertEquals('Chiens', $pageEleveurBranch->getCommit()->getEspeces());
        $this->assertEquals('Chihuahua', $pageEleveurBranch->getCommit()->getRaces());
        $this->assertEquals('Hauts-de-Seine', $pageEleveurBranch->getCommit()->getLieu());
        $this->assertEquals('Nouvelle portée', $pageEleveurBranch->getCommit()->getActualites()->first()->getContenu());
    }

    public function testMappingBranchToModel()
    {
        // Mock d'une page eleveur en base de données
        $user = new User();
        $user->setId(1);
        $pageEleveurBranch = new PageEleveurBranch();
        $pageEleveurBranch->setId(1);
        $pageEleveurBranch->setOwner($user);

        $this->pageEleveurBranchRepository
            ->method('findBySlug')->withAnyParameters()->willReturn($pageEleveurBranch);

        $commit = new PageEleveurCommit(null, 'Tatouine', 'Plein de chartreux', 'Chats', 'Chartreux', 'Roubaix',
            null,
            [new Actualite('Nouvelle portée')]
        );

        $pageEleveurBranch->setCommit($commit);

        $this->pageEleveurCommitRepository
            ->method('find')->with($commit->getId())->willReturn($commit);

        $pageEleveur = $this->pageEleveurService->findBySlug('');

        $this->assertEquals('Tatouine', $pageEleveur->getNom());
        $this->assertEquals('Plein de chartreux', $pageEleveur->getDescription());
        $this->assertEquals('Chats', $pageEleveur->getEspeces());
        $this->assertEquals('Chartreux', $pageEleveur->getRaces());
        $this->assertEquals('Roubaix', $pageEleveur->getLieu());
        $this->assertEquals('Nouvelle portée', $pageEleveur->getActualites()[0]->getContenu());
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::NON_FAST_FORWARD
     */
    public function testCommit_NonFastForward()
    {
        $user = new User();
        $user->setId(1);
        $pageEleveurBranch = new PageEleveurBranch();
        $pageEleveurBranch->setId(1);
        $pageEleveurBranch->setOwner($user);

        $this->pageEleveurBranchRepository
            ->method('find')->withAnyParameters()->willReturn($pageEleveurBranch);

        // commit1 est l'avant dernier etat de la page
        $commit1 = $this->newCommit(1);

        // commit2 est l'état courant de la page, il descend de commit1
        $commit2 = $this->newCommit(2, $commit1);
        $pageEleveurBranch->setCommit($commit2);

        $this->pageEleveurCommitRepository
            ->method('find')->willReturn($commit1);

        $pageEleveur = new PageEleveur();
        $pageEleveur->setId($pageEleveurBranch->getId());
        $pageEleveur->setHead($commit1->getId());

        // le commit sur commit3 doit échouer car il n'est pas fastforward depuis commit2
        $this->pageEleveurService->commit($user, $pageEleveur);
    }

    public function testSlug()
    {
        // conservation des caractères de base
        $this->assertEquals('azertyuiopqsdfghjklmwxcvbn1234567890', PageEleveurService::slug('azertyuiopqsdfghjklmwxcvbn1234567890'));

        // trim
        $this->assertEquals('aaa', PageEleveurService::slug(' aaa '));

        // to lowercase
        $this->assertEquals('aaa', PageEleveurService::slug('AaA'));

        // remplacement des caractères convertibles
        $this->assertEquals('eureace', PageEleveurService::slug('€éàçè&'));

        // espaces convertis en dash
        $this->assertEquals('un-deux-trois', PageEleveurService::slug('un deux trois'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSlugVide()
    {
        PageEleveurService::slug('!?,.<>=&');
    }
}