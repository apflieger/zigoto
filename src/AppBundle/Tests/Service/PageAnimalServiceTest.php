<?php

namespace AppBundle\Tests\Service;

use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalBranch;
use AppBundle\Entity\PageAnimalCommit;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use AppBundle\Service\PageAnimalService;
use AppBundle\Tests\TestTimeService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Config\FileLocator;

class PageAnimalServiceTest extends KernelTestCase
{
    /** @var PageAnimalService */
    private $pageAnimalService;

    /** @var PageAnimalBranchRepository|PHPUnit_Framework_MockObject_MockObject */
    private $pageAnimalBranchRepository;

    /** @var EntityRepository|PHPUnit_Framework_MockObject_MockObject */
    private $pageAnimalCommitRepository;

    /** @var TestTimeService */
    private $timeService;

    public function setup()
    {
        $this->pageAnimalBranchRepository = $this->getMockBuilder(PageAnimalBranchRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var EntityManager|PHPUnit_Framework_MockObject_MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageAnimalCommitRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        static::bootKernel();
        /** @var FileLocator $fileLocator */
        $fileLocator = static::$kernel->getContainer()->get('file_locator');
        $this->timeService = self::$kernel->getContainer()->get('zigotoo.time');

        $this->pageAnimalService = new PageAnimalService(
            $entityManager,
            $this->pageAnimalBranchRepository,
            $this->pageAnimalCommitRepository,
            $fileLocator,
            $this->timeService
        );
    }

    public function testCreate_Success()
    {
        $owner = new User();
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->pageAnimalService->create($owner);

        $this->assertEquals($owner, $pageAnimal->getOwner());
    }

    public function testCommit_fastforward()
    {

        // Simulation d'une page animal en base de données
        $user = new User();
        $pageAnimalBranch = new PageAnimalBranch();

        $pageAnimalBranch->setOwner($user);
        $commit = new PageAnimalCommit(null, 'rodolf', $this->timeService->now(), null);
        $commit->setId(1);
        $pageAnimalBranch->setCommit($commit);

        $this->pageAnimalBranchRepository->method('find')->willReturn($pageAnimalBranch);
        $this->pageAnimalCommitRepository->method('find')->with($commit->getId())->willReturn($commit);

        $this->timeService->lockNow($this->timeService->now()->add(new \DateInterval("P1D")));
        // Simulation d'un commit coté client sur la page animal
        $pageAnimal = new PageAnimal();
        $pageAnimal->setHead($commit->getId());
        $pageAnimal->setNom('rudolf');
        $pageAnimal->setDateNaissance($this->timeService->now());
        $pageAnimal->setDescription('Inscrit au LOF');
        $this->pageAnimalService->commit($user, $pageAnimal);

        // On vérifier que le commit a bien été créé avec les nouvelles données
        $this->assertEquals($commit->getId(), $pageAnimalBranch->getCommit()->getParent()->getId());
        $this->assertEquals('rudolf', $pageAnimalBranch->getCommit()->getNom());
        $this->assertEquals($this->timeService->now(), $pageAnimalBranch->getCommit()->getDateNaissance());
        $this->assertEquals('Inscrit au LOF', $pageAnimalBranch->getCommit()->getDescription());
    }

    public function testMappingBranchToModel()
    {
        $this->timeService->lockNow();

        // mock d'une page animal en bdd
        $pageAnimalBranch = new PageAnimalBranch();
        $pageAnimalBranch->setId(1);
        $this->pageAnimalBranchRepository->method('find')->with(1)->willReturn($pageAnimalBranch);

        $commit = new PageAnimalCommit(null, 'rudy', $this->timeService->now(), 'Un beau chien');
        $pageAnimalBranch->setCommit($commit);

        // Requete de la page animal
        $pageAnimal = $this->pageAnimalService->find(1);

        // On vérifie que la page est retournée avec les bonnes données
        $this->assertEquals('rudy', $pageAnimal->getNom());
        $this->assertEquals($this->timeService->now(), $pageAnimal->getDateNaissance());
        $this->assertEquals('Un beau chien', $pageAnimal->getDescription());
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::NON_FAST_FORWARD
     */
    public function testCommit_non_fastforward()
    {
        // Simulation d'une branche en bdd sur le commit 1
        $user = new User();
        $user->setId(1);
        $pageAnimalBranch = new PageAnimalBranch();
        $pageAnimalBranch->setOwner($user);
        $commit = new PageAnimalCommit(null, 'Joey', null, null);
        $commit->setId(1);
        $pageAnimalBranch->setCommit($commit);

        $this->pageAnimalBranchRepository->method('find')->with(10)->willReturn($pageAnimalBranch);

        // Simulation commit à partir d'un head sur le commit 2
        $pageAnimal = new PageAnimal();
        $pageAnimal->setHead(2);
        $pageAnimal->setId(10);
        $this->pageAnimalCommitRepository->method('find')->with(2)
            ->willReturn(new PageAnimalCommit(null, 'Joey', null, null));
        $this->pageAnimalService->commit($user, $pageAnimal);
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     * @expectedExceptionCode \AppBundle\Service\HistoryException::DROIT_REFUSE
     */
    public function testCommit_pas_owner_droit_refuse()
    {
        $user = new User();
        $user->setId(1);
        $pageAnimalBranch = new PageAnimalBranch();
        $pageAnimalBranch->setOwner($user);

        $this->pageAnimalBranchRepository->method('find')->willReturn($pageAnimalBranch);

        $pageAnimal = new PageAnimal();
        $user2 = new User();
        $user2->setId(2);
        $this->pageAnimalService->commit($user2, $pageAnimal);
    }

    /**
     * @expectedException \AppBundle\Service\ValidationException
     * @expectedExceptionCode \AppBundle\Service\ValidationException::EMPTY_NOM
     */
    public function testCommit_nom_empty()
    {
        // Simulation d'une page animal en base de données
        $user = new User();
        $pageAnimalBranch = new PageAnimalBranch();

        $pageAnimalBranch->setOwner($user);
        $commit = new PageAnimalCommit(null, 'rodolf', $this->timeService->now(), null);
        $commit->setId(1);
        $pageAnimalBranch->setCommit($commit);

        $this->pageAnimalBranchRepository->method('find')->willReturn($pageAnimalBranch);
        $this->pageAnimalCommitRepository->method('find')->with($commit->getId())->willReturn($commit);

        // Commit la page avec un nom vide
        $pageAnimal = $this->pageAnimalService->find($pageAnimalBranch->getId());
        $pageAnimal->setNom('');
        $this->pageAnimalService->commit($user, $pageAnimal);
    }

    /**
     * @expectedException \AppBundle\Service\ValidationException
     * @expectedExceptionCode \AppBundle\Service\ValidationException::EMPTY_DATE_NAISSANCE
     */
    public function testCommit_dateNaissance_empty()
    {
        // Simulation d'une page animal en base de données
        $user = new User();
        $pageAnimalBranch = new PageAnimalBranch();

        $pageAnimalBranch->setOwner($user);
        $commit = new PageAnimalCommit(null, 'rodolf', $this->timeService->now(), null);
        $commit->setId(1);
        $pageAnimalBranch->setCommit($commit);

        $this->pageAnimalBranchRepository->method('find')->willReturn($pageAnimalBranch);
        $this->pageAnimalCommitRepository->method('find')->with($commit->getId())->willReturn($commit);

        // Commit la page avec un nom vide
        $pageAnimal = $this->pageAnimalService->find($pageAnimalBranch->getId());
        $pageAnimal->setDateNaissance(null);
        $this->pageAnimalService->commit($user, $pageAnimal);
    }
}
