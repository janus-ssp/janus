<?php

use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.

// Custom: require Vhost to state that this can be used by setting:
//     SetEnv SFDEV 1
//if (!getenv('SFDEV')) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

$loader = require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
sspmod_janus_DiContainer::registerAppKernel($kernel);

Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
