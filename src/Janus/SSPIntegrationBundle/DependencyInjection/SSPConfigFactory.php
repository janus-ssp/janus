<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\SSPIntegrationBundle\DependencyInjection;

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
     * @var string $environment
     */
    public function __construct($environment)
    {
        $this->setPathsToConfig($environment);
    }

    /**
     * Sets a list of possible paths where the janus module config file can reside.
     *
     * Since janus can be installed in various ways the config file location has to be determined.
     */
    private function setPathsToConfig($environment)
    {
        $rootDir = realpath(__DIR__ . '/../../../../');

        if ($environment == 'test') {
            $this->pathsToConfigs = array(
                realpath($rootDir . '/config-templates/module_janus.php') // Shipped config template
            );
            return;
        }

        $this->pathsToConfigs = array(
            realpath($rootDir . '/../../config/module_janus.php'), // Janus installed in SimpleSamlPhp module dir
            realpath($rootDir . '/../../simplesamlphp/simplesamlphp/config/module_janus.php'), // Janus installed alongside SimpleSamlPhp in vendor
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
        return new SimpleSAML_Configuration($config, $pathToConfig);
    }

    /**
     * Tries to find a matching path to janus config
     *
     * @return mixed
     */
    private function findPathToConfig()
    {
        foreach ($this->pathsToConfigs as $pathToConfig) {
            if (file_exists($pathToConfig)) {
                return $pathToConfig;
            }
        }
    }
}