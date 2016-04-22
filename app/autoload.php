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

Type::overrideType('datetime', LocalDateTimeType::class);
Type::overrideType('datetimetz', LocalDateTimeType::class);

return $loader;
