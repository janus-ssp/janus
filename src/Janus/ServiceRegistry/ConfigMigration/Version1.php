<?php
namespace Janus\ServiceRegistry\ConfigMigration\Version1;

use RuntimeException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class Version1
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Dumper
     */
    private $yamlDumper;

    /**
     *
     */
    public function __construct()
    {
        $this->parameters = array();
        $this->rootDir = realpath(__DIR__ . "/../../../..");

        $this->yamlDumper = new \Symfony\Component\Yaml\Dumper();
    }

    public function dump()
    {
        // Read current parameters as default
        $parametersFile = $this->rootDir . '/app/config/parameters.yml';

        $this->loadParameterDefaults($parametersFile);

        $config = $this->loadConfig();
        // @todo fix database parameters
        // @todo fix replace _DOT_ back to '.'
        $config = $this->removeCacheAndLogsDirs($config);
        $config = $this->correctDotsInMetadatafields($config);
        $config = $this->correctDotsInPaths($config);
        $config = $this->correctAccessConfig($config);
        $config = $this->correctWorkflow($config);

        $config = $this->wrapConfigInNamespace($config);

        $this->writeParametersToFile($parametersFile);

        $configFile = $this->rootDir . '/app/config/config_custom.yml';
        $this->writeConfigToFile($config, $configFile);
    }

    /**
     * @param string $parametersFile
     * @throws \RuntimeException
     */
    private function loadParameterDefaults($parametersFile)
    {
        $isWritable = false;
        $yamlParser = new Parser();

        $files = array(
            $parametersFile . '.dist',
            $parametersFile
        );
        foreach ($files as $file) {
            if (!is_readable($file)) {
                continue;
            }

            $defaultsYaml = file_get_contents($file);
            $parameters = $yamlParser->parse($defaultsYaml);
            if (isset($parameters['parameters'])) {
                $this->parameters = array_merge($this->parameters, $parameters['parameters']);
            }
        }
    }

    private function loadConfig()
    {
        require $this->rootDir . '/config-templates/module_janus.php';
        return $config;
    }

    /**
     * Removes no longer used cache and logs dirs.
     *
     * @param array $config
     * @return array
     */
    private function removeCacheAndLogsDirs(array $config) {
        unset($config['cache_dir']);
        unset($config['logs_dir']);

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
        if (is_int($path)) {
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

    private function correctDotsInMetadatafields(array $config)
    {
        $newConfig = array();
        foreach ($config as $entryName => $entryConfig) {
            $prefix = 'metadatafields';
            $isListOfMetadataFields = substr($entryName, 0, strlen($prefix)) === $prefix && is_array($entryConfig);
            if (!$isListOfMetadataFields) {
                continue;
            }

            foreach ($entryConfig as $fieldName => $fieldConfig) {
                $cleanFieldName = str_replace('.', '_DOT_', $fieldName);
                unset($config[$entryName][$fieldName]);
                $config[$entryName][$cleanFieldName] = $fieldConfig;
            }
        }

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

    /**
     * @param array $config
     * @return array
     */
    private function correctWorkflow(array $config)
    {
        $config['workflow'] = $config['workflow_states'];
        unset($config['workflow_states']);
        return $config;
    }

    private function writeParametersToFile($parametersFile)
    {
        $isWritable = (file_exists($parametersFile) && is_writable($parametersFile)) ||
            is_writable(dirname($parametersFile));

        if (!$isWritable) {
            throw new RuntimeException('parameters file is not writable');
        }

        $parametersYaml = $this->yamlDumper->dump(array('parameters' => $this->parameters), 10);
        file_put_contents($parametersFile, $parametersYaml);
    }

    private function writeConfigToFile($config, $configFile)
    {
        $configYaml = $this->yamlDumper->dump($config, 10);
        file_put_contents($configFile, $configYaml);
    }
}
