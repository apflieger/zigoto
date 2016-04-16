<?php

namespace AppBundle\Tests\Service;

use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalBranch;
use AppBundle\Entity\PageAnimalCommit;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use AppBundle\Service\PageAnimalService;
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

        $this->pageAnimalService = new PageAnimalService(
            $entityManager,
            $this->pageAnimalBranchRepository,
            $this->pageAnimalCommitRepository,
            $fileLocator
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
        $commit = new PageAnimalCommit(null, 'rodolf');
        $commit->setId(1);
        $pageAnimalBranch->setCommit($commit);

        $this->pageAnimalBranchRepository->method('find')->willReturn($pageAnimalBranch);
        $this->pageAnimalCommitRepository->method('find')->with($commit->getId())->willReturn($commit);

        // Simulation d'un commit coté client sur la page animal
        $pageAnimal = new PageAnimal();
        $pageAnimal->setHead($commit->getId());
        $pageAnimal->setNom('rudolf');
        $this->pageAnimalService->commit($user, $pageAnimal);

        // On vérifier que le commit a bien été créé avec les nouvelles données
        $this->assertEquals($commit->getId(), $pageAnimalBranch->getCommit()->getParent()->getId());
        $this->assertEquals('rudolf', $pageAnimalBranch->getCommit()->getNom());
    }
}