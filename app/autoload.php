<?php

use AppBundle\Entity\LocalDateTimeType;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

use Doctrine\DBAL\Types\Type;
use AppBundle\Entity\PostGresqlArrayType;

Type::overrideType(Type::DATETIME, LocalDateTimeType::class);
Type::overrideType(Type::DATETIMETZ, LocalDateTimeType::class);
Type::overrideType(Type::TARRAY, PostGresqlArrayType::class);

return $loader;
