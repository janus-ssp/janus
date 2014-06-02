<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 * @todo See if this can be removed
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use SimpleSAML_Configuration;

class SSPConfigFactory
{
    /**
     * List of possible paths where the janus module config file can reside
     *
     * @var array
     */
    private $pathsToConfigs;

    /**
     * @var array
     */
    private static $installConfig;

    /**
     * @param $environment
     * @return SimpleSAML_Configuration
     */
    public static function getInstance($environment)
    {
        /** @var $s_instances SimpleSAML_Configuration[] */
        static $s_instances = array();

        if (!isset($s_instances[$environment])) {
            /** @var SSPConfigFactory $factory */
            $factory = new static($environment);
            $s_instances[$environment] = $factory->create();
        }

        return $s_instances[$environment];
    }

    /**
     * @var string $environment
     */
    public function __construct($environment)
    {
        $this->setPathsToConfig($environment);
    }

    /**
     * Sets config specifically for overrides during install
     *
     * @param array $installConfig
     */
    public static function setInstallConfig(array $installConfig)
    {
        static::$installConfig = $installConfig;
    }

    /**
     * Sets a list of possible paths where the janus module config file can reside.
     *
     * Since janus can be installed in various ways the config file location has to be determined.
     */
    private function setPathsToConfig($environment)
    {
        $rootDir = realpath(__DIR__ . '/../../../../../../');
        $shippedConfigTemplatePath = realpath($rootDir . '/config-templates/module_janus.php');

        if ($environment == 'test') {
            $this->pathsToConfigs = array(
                $shippedConfigTemplatePath
            );
            return;
        }

        $this->pathsToConfigs = array(
            realpath($rootDir . '/../../config/module_janus.php'), // Janus installed in SimpleSamlPhp module dir
            realpath($rootDir . '/../../simplesamlphp/simplesamlphp/config/module_janus.php'), // Janus installed alongside SimpleSamlPhp in vendor
            $shippedConfigTemplatePath
        );
    }

    /**
     * @return SimpleSAML_Configuration
     */
    public function create()
    {
        // Configure this class as a singleton

        $pathToConfig = $this->findPathToConfig();
        $config = array();
        require $pathToConfig;

        $config = $this->overideConfigForInstall($config);

        return new SimpleSAML_Configuration($config, $pathToConfig);
    }

    /**
     * Overrides config values for installation purposes.
     *
     * @param array $config
     */
    private function overideConfigForInstall(array $config)
    {
        if (!is_array(static::$installConfig)) {
            return $config;
        }

        return array_merge($config, static::$installConfig);
    }

    /**
     * Tries to find a matching path to janus config
     *
     * @return mixed
     * @throws \Exception
     */
    private function findPathToConfig()
    {
        foreach ($this->pathsToConfigs as $pathToConfig) {
            if (file_exists($pathToConfig)) {
                return $pathToConfig;
            }
        }

        throw new \Exception('No config file found');
    }
}
