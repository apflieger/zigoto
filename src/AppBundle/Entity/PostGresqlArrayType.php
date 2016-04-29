<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\Type;

/**
 * On surcharge le type 'array' qui, de base, pose un problème avec postgresql.
 * La sérialisation utilisée comporte des caractères \0 qui ne sont pas supportés par pg.
 */
class PostGresqlArrayType extends ArrayType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value))
            return null;
        /** @var array $photoArray */
        $serialPhotos = unserialize(str_replace('[NULL]', "\0", $value));

        return $serialPhotos;
    }

    /**
     * @param ArrayCollection $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $serialized = serialize($value);
        // serialize met des \0 dans le format ce qui n'est pas supporté par postgres
        $serialized = str_replace("\0", '[NULL]', $serialized);
        return $serialized;
    }
}