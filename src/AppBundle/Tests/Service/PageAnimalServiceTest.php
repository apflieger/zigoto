<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 21:46
 */

namespace AppBundle\Tests\Service;


use AppBundle\Entity\PageAnimal;
use AppBundle\Entity\User;
use AppBundle\Service\PageAnimalService;
use PHPUnit_Framework_TestCase;

class PageAnimalServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var PageAnimalService */
    private $pageAnimalService;

    public function __construct()
    {
        $this->pageAnimalService = new PageAnimalService();
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateNomVide()
    {
        $this->pageAnimalService->create('', new User());
    }

    public function testCreateSuccess()
    {
        /** @var PageAnimal $pageAnimal */
        $pageAnimal = $this->pageAnimalService->create('Boule de neige', new User());

        $this->assertEquals('boule-de-neige', $pageAnimal->getSlug());
    }
}