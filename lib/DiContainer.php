<?php
define("JANUS_ROOT_DIR", dirname(dirname(__FILE__)));

// Find out if janus has it's own vendor dir or is installed as composer dependency of a project
$projectVendorDir = realpath(JANUS_ROOT_DIR . '/../../../vendor');
if (is_dir($projectVendorDir)) {
    define("VENDOR_DIR", $projectVendorDir);
} else {
    define("VENDOR_DIR", JANUS_ROOT_DIR . '/vendor');
}

require_once VENDOR_DIR . "/autoload.php";

class sspmod_janus_DiContainer extends Pimple
{
    const CONFIG = 'config';

    /** @var sspmod_janus_DiContainer */
    private static $instance;

    public function __construct()
    {
    }

    /**
     * @return sspmod_janus_DiContainer
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof sspmod_janus_DiContainer) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}