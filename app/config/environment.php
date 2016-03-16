<?php

function get($key)
{
    return getenv($key) ? getenv($key) : '';
}

$container->setParameter('kernel.user',         get('SYMFONY_USER'));

$container->setParameter('database_host',       get('POSTGRESQL_ADDON_HOST'));
$container->setParameter('database_port',       get('POSTGRESQL_ADDON_PORT'));
$container->setParameter('database_user',       get('POSTGRESQL_ADDON_USER'));
$container->setParameter('database_password',   get('POSTGRESQL_ADDON_PASSWORD'));
$container->setParameter('database_name',       get('POSTGRESQL_ADDON_DB'));
