<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:46
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageAnimalCommit;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalBranchRepository;
use AppBundle\Service\PageAnimalService;
use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class PageAnimalServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var PageAnimalService */
    private $pageAnimalService;

    /** @var PageAnimalBranchRepository|PHPUnit_Framework_MockObject_MockObject */
    private $pageAnimalRepository;

    public function setup()
    {
        $this->pageAnimalRepository = $this->getMockBuilder(PageAnimalBranchRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var EntityManager|PHPUnit_Framework_MockObject_MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageAnimalService = new PageAnimalService($entityManager, $this->pageAnimalRepository);
    }

    public function testCreate_Success()
    {
        $owner = new User();
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->pageAnimalService->create($owner);

        $this->assertEquals($owner, $pageAnimal->getOwner());
    }

    public function testCreate_PlusieursAnimaux()
    {
        $this->pageAnimalRepository->method('findByOwner')->willReturn([new PageEleveur()]);
        $this->pageAnimalService->create(new User());
    }
}