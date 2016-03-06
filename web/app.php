<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$env = getenv('SYMFONY_ENV');
$debug = getenv('SYMFONY_DEBUG');

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

if ($env === 'prod') {
    if ($_SERVER['HTTP_HOST'] !== 'www.zigotoo.com') {
        header('HTTP/1.0 403 Forbidden');
        exit('Environnement de prod sur host '. $_SERVER['HTTP_HOST']. ', c\'est bizarre... Voir ' . __FILE__);
    }

    // Enable APC for autoloading to improve performance.
    // You should change the ApcClassLoader first argument to a unique prefix
    // in order to prevent cache key conflicts with other applications
    // also using APC.
    /*
    $apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
    $loader->unregister();
    $apcLoader->register(true);
    */
}

if ($debug) {
    Debug::enable();
}

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel($env, $debug);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
