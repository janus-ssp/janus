<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

require_once __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../config-templates/module_janus.php";

function set($path, $value, &$target)
{
    if (empty($path)) {
        throw new InvalidArgumentException("Path should not be empty");
    }

    if (!is_string($path)) {
        throw new InvalidArgumentException("Path is a '" . gettype($path) . "', expected a string");
    }

    $pathParts = explode(".", $path);
    $target =& $target;
    do {
        $partName = array_shift($pathParts);

        // Store value if path is found
        if (empty($pathParts)) {
            $target[$partName] = $value;
            return;
        }

        // Get reference to nested child
        if (!array_key_exists($partName, $target)) {
            $target[$partName] = array();
        }
        $target =& $target[$partName];
    } while (true);
}

$newConfig = array(
    'janus_service_registry_core' => array()
);
foreach ($config as $key => $val) {
    set($key, $val, $newConfig['janus']);
}

$yamlDumper = new \Symfony\Component\Yaml\Dumper();
echo $yamlDumper->dump($newConfig, 10, 10);
