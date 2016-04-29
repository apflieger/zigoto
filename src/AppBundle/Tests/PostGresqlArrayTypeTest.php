<?php


namespace AppBundle\Tests;


use AppBundle\Entity\Photo;
use AppBundle\Entity\PostGresqlArrayType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PostGresqlArrayTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var Type */
    private $type;

    /** @var AbstractPlatform */
    private $plateform;

    public function setup()
    {
        $this->type = Type::getType(Type::TARRAY);
        $this->plateform = $this->getMockBuilder(AbstractPlatform::class)->getMock();
    }

    public function testDoctrineArrayOverriden()
    {
        $this->assertInstanceOf(PostGresqlArrayType::class, $this->type);
    }

    public function testConvertToPHPValue_null()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->plateform));
    }

    public function testConvertToPHPValue_empty()
    {
        $this->assertNull($this->type->convertToPHPValue('', $this->plateform));
    }

    public function testConvertToPHPValue_string()
    {
        $this->assertEquals('haystack', $this->type->convertToPHPValue(serialize('haystack'), $this->plateform));
    }

    public function testArray()
    {
        $photo = new Photo();
        $photo->setNom('wohoo');

        $serialized = $this->type->convertToDatabaseValue([$photo], $this->plateform);
        $unserialized = $this->type->convertToPHPValue($serialized, $this->plateform);

        $this->assertInstanceOf(Photo::class, $unserialized[0]);

        /** @var Photo $var */
        $var = $unserialized[0];
        $this->assertEquals('wohoo', $var->getNom());
    }
}