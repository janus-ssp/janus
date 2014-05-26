<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
require_once __DIR__ . "/../vendor/autoload.php";

class ConfigToYmlConverter
{
    public function dump()
    {
        $config = $this->loadConfig();
        // @todo think of a fix for metadatafields like redirect.sign
        $config = $this->correctDotsInPaths($config);
        $config = $this->correctAccessConfig($config);
        $config = $this->wrapConfigInNamespace($config);

        $yamlDumper = new \Symfony\Component\Yaml\Dumper();
        echo $yamlDumper->dump($config, 10);
    }

    private function loadConfig()
    {
        require __DIR__ . "/../config-templates/module_janus.php";
        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    private function wrapConfigInNamespace($config)
    {
        return array(
            'janus_service_registry_core' => $config
        );
    }

    /**
     * @param string $path
     * @param mixed $value
     * @param array &$target
     * @throws InvalidArgumentException
     */
    private function set($path, $value, &$target)
    {
        if(is_int($path)) {
            $target[$path] = $value;
            return;
        }

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

    /**
     * Wrap roles in role namespace to prevent mixing booleans and arrays.
     *
     * @param array $config
     */
    private function correctAccessConfig(array $config)
    {
        $parsedRights = array();
        foreach ($config['access'] as $rightName => $workflowStates) {
            foreach ($workflowStates as $state => $rights) {
                if ($state === 'default') {
                    $parsedRights[$rightName]['default'] = $rights;
                } else {
                    if ($state === 'role') {
                        $parsedRights[$rightName]['workflow_states']['all'] = $rights;
                    } else {
                        $parsedRights[$rightName]['workflow_states'][$state] = $rights['role'];
                    }
                }
            }
        }
        $config['access'] = $parsedRights;

        return $config;
    }

    /**
     * Symfony cannot handle dots in config keys so config needs to be nested instead
     *
     * @param array $config
     */
    private function correctDotsInPaths(array $config)
    {
        $newConfig = array();
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                $v = $this->correctDotsInPaths($v);
            }
            $this->set($k, $v, $newConfig);
        }
        return $newConfig;
    }
}

$converter = new ConfigToYmlConverter();
$converter->dump();
