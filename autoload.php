<?php
define("JANUS_ROOT_DIR", __DIR__);

// Since janus can be installed both in the modules dir of simplesaml or in vendor dir of a different project
// Find out if janus has it's own vendor dir or is installed as composer dependency of a project
$projectVendorDir = realpath(JANUS_ROOT_DIR . '/../../../vendor');
define('IS_JANUS_INSTALLED_AS_COMPOSER_DEPENDENCY', is_dir($projectVendorDir));
if (IS_JANUS_INSTALLED_AS_COMPOSER_DEPENDENCY) {
    define("VENDOR_DIR", $projectVendorDir);
    define("SIMPLESAMLPHP_DIR", VENDOR_DIR .   '/simplesamlphp/simplesamlphp');
} else {
    define("VENDOR_DIR", JANUS_ROOT_DIR . '/vendor');
    define("SIMPLESAMLPHP_DIR", realpath(JANUS_ROOT_DIR . '/../../'));
}

// Load SimpleSamlPhp Autoloader
require_once SIMPLESAMLPHP_DIR . "/lib/_autoload.php";

// Load Composer autoloader
return require_once VENDOR_DIR . "/autoload.php";