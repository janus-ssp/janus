<?php
require_once __DIR__ . "/../vendor/autoload.php";

// @todo convert this to a proper migration
$converter = new \Janus\ServiceRegistry\ConfigMigration\Version1\Version1();
$converter->dump();
