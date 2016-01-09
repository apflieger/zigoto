<?php
/**
 * Created by PhpStorm.
 * User: arnaudpflieger
 * Date: 09/01/2016
 * Time: 23:15
 */

namespace AppBundle\Tests\Service;


use AppBundle\Service\HistoryService;
use PHPUnit_Framework_TestCase;

class HistoryServiceTest extends PHPUnit_Framework_TestCase
{
    public function testSlug()
    {
        // conservation des caractères de base
        $this->assertEquals('azertyuiopqsdfghjklmwxcvbn1234567890', HistoryService::slug('azertyuiopqsdfghjklmwxcvbn1234567890'));

        // trim
        $this->assertEquals('aaa', HistoryService::slug(' aaa '));

        // to lowercase
        $this->assertEquals('aaa', HistoryService::slug('AaA'));

        // remplacement des caractères convertibles
        $this->assertEquals('eureace', HistoryService::slug('€éàçè&'));

        // espaces convertis en dash
        $this->assertEquals('un-deux-trois', HistoryService::slug('un deux trois'));
    }

    /**
     * @expectedException \AppBundle\Service\HistoryException
     */
    public function testSlugVide()
    {
        HistoryService::slug('!?,.<>=&');
    }
}