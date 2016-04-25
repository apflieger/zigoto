<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PhotoArrayType extends Type
{
    const PHOTO_ARRAY_TYPE = 'photoarray'; // modify to match your type name

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
        // Repompé de ArrayType
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value))
            return new ArrayCollection([]);
        /** @var array $photoArray */
        $serialPhotos = unserialize(str_replace('[NULL]', "\0", $value));

        return new ArrayCollection($serialPhotos);
    }

    /**
     * @param ArrayCollection $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $serialized = serialize($value->toArray());
        // serialize met des \0 dans le format ce qui n'est pas supporté par postgres
        $serialized = str_replace("\0", '[NULL]', $serialized);
        return $serialized;
    }

    public function getName()
    {
        return self::PHOTO_ARRAY_TYPE;
    }
}