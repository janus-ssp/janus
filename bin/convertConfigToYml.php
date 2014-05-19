<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

require_once __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../config-templates/module_janus.php";

$yamlDumper = new \Symfony\Component\Yaml\Dumper();
echo $yamlDumper->dump($config, 10, 10);
