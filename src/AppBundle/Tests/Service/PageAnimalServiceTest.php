<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:46
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\PageEleveur;
use AppBundle\Entity\User;
use AppBundle\Repository\PageAnimalRepository;
use AppBundle\Service\PageAnimalService;
use PHPUnit_Framework_TestCase;

class PageAnimalServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var PageAnimalService */
    private $pageAnimalService;

    /** @var PageAnimalRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $pageAnimalRepository;

    public function setup()
    {
        $this->pageAnimalRepository = $this->getMockBuilder('AppBundle\Repository\PageAnimalRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageAnimalService = new PageAnimalService($this->pageAnimalRepository);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreate_NomVide()
    {
        $this->pageAnimalService->create('', new User());
    }

    public function testCreate_Success()
    {
        $owner = new User();
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->pageAnimalService->create('Boule de neige', $owner);

        $this->assertEquals('boule-de-neige', $pageAnimal->getSlug());
        $this->assertEquals($owner, $pageAnimal->getOwner());
    }

    public function testCreate_PlusieursAnimaux()
    {
        $this->pageAnimalRepository->method('findByOwner')->willReturn([new PageEleveur()]);
        $this->pageAnimalService->create('test', new User());
    }
}