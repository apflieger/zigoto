<?php

# C'est le point d'entré de app/console server:run

putenv('SYMFONY_ENV=dev');
putenv('SYMFONY_DEBUG=1');

require_once __DIR__.'/app.php';
