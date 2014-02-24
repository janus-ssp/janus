<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;


$janusRootDir = dirname(__DIR__);

/**
 * @var ClassLoader $loader
 */
$loader = requireLoaders($janusRootDir);

if(!defined('JANUS_ROOT_DIR')) {
    define('JANUS_ROOT_DIR', $janusRootDir);

    // intl
    if (!function_exists('intl_get_error_code')) {
        require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
    }

    AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
}

return $loader;

/**
 * Loads Composer and SimpleSampPhp autoloaders.
 *
 * @param string $janusRootDir
 * @return mixed
 * @todo merge with symfony app/autoload.php
 */
function requireLoaders($janusRootDir)
{
    /** Since janus can be installed both in the modules dir of simplesaml
     * or in vendor dir of a main project some 'magic' is required to find fix autoloading
     */
    $vendorDir = findVendorDir($janusRootDir);

    /**
     * When SimpleSamlPhp is not installed using composer it's own custom autoloader needs to be
     * required
     */
    if (!isSimpleSamlPhpInstalledInVendor($vendorDir)) {
        $simpleSamlPhpDir = realpath($janusRootDir . '/../../../');
        require_once $simpleSamlPhpDir . "/lib/_autoload.php";
    }

    // Load Composer autoloader
    $composerAutoLoader = require $vendorDir . "/autoload.php";
    return $composerAutoLoader;
}

/**
 * Checks if janus has it's own vendor dir,.
 *
 * This is the case when:
 * - running an install from archive
 * - developing on Janus API
 * - running tests on CI server
 *
 * @param string $janusRootDir
 * @return string
 */
function findVendorDir($janusRootDir)
{
    $janusVendorDir = $janusRootDir . '/vendor';
    if (is_dir($janusVendorDir)) {
        return $janusVendorDir;
    }

    /**
     * Find out if janus is installed as composer dependency of a main project
     * This is the case when using sample as part of a bigger project like for
     * example the OpenConext ServiceRegistry
     */
    $mainProjectVendorDir = realpath($janusRootDir . '/../../../vendor');
    if (is_dir($mainProjectVendorDir)) {
        return $mainProjectVendorDir;
    }
}

/**
 * Checks if SimpleSamlPhp is installed in the vendor dir using composer
 *
 * Note that it's also possible that a vendor dir exists within janus without SimpleSamlPhp
 * This is the case when installing from archive.
 *
 * @param string $vendorDir
 * @return bool
 */
function isSimpleSamlPhpInstalledInVendor($vendorDir)
{
    $simpleSamlPhpDirInVendor = $vendorDir . '/simplesamlphp/simplesamlphp/';
    return is_dir($simpleSamlPhpDirInVendor);
}