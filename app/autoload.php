<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * Note that this can be called in 2 ways:
 * * Standalone, via the new REST API, in which case we need to autoload the parent SSP so we have access to
 *   SSP utilities like SimpleSAML_Configuration that we still use.
 * * As an include of SSPs module.php in which case SSP has already been loaded.
 */
define('SIMPLESAML_AUTOLOADER', __DIR__ . '/../../../lib/_autoload.php');

if (!class_exists('SimpleSAML_Configuration', true) && file_exists(SIMPLESAML_AUTOLOADER)) {
    require_once SIMPLESAML_AUTOLOADER;
}

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;