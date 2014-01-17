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

    public function __construct()
    {
        $this->setPathsToConfig();
    }

    /**
     * Sets a list of possible paths where the janus module config file can reside.
     *
     * Since janus can be installed in various ways the config file location has to be determined.
     */
    private function setPathsToConfig()
    {
        $rootDir = realpath(__DIR__ . '/../../../../');
        $this->pathsToConfigs = array(
            realpath($rootDir . '/../../config/module_janus.php'), // Janus installed in SimpleSamlPhp module dir
            realpath($rootDir . '/../../simplesamlphp/simplesamlphp/config/module_janus.php'), // Janus installed alongside SimpleSamlPhp in vendor
            realpath($rootDir . 'config-templates/module_janus.php') // shipped config template
        );
    }

    /**
     * @return SimpleSAML_Configuration
     */
    public function create()
    {
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