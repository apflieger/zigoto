<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Cette classe sert à convertir les DateTime en Europe/Paris avant insertion en base.
 *
 * Le problème est que la bdd ne persiste pas la timezone et les date qu'on y met
 * ne sont pas forcement en Europe/Paris. En particulier celles qui viennent du JS.
 * On a donc besoin de standardiser la timezone avant de persister les datetimes
 * pour pouvoir les relire. A la lecture, les date sont créées dans la timezone
 * par défaut qui correspond bien à la timezone dans laquelle la date a été ecrite en base.
 *
 * Voir http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html#default-timezone-gotcha
 *
 * Class UTCDateTimeType
 * @package AppBundle\Entity
 */
class LocalDateTimeType extends DateTimeType
{
    static private $timezone;

    private static function getTimeZone()
    {
        return self::$timezone ? self::$timezone : self::$timezone = new \DateTimeZone(ini_get('date.timezone'));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value->setTimezone(self::getTimeZone());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}