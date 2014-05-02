<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat\DbConfigParser;
use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat\MemcacheConfigParser;
use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\Configuration;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

use SimpleSAML_Configuration;

/**
 * @todo find out why this class (only) works when using the full name class and short name filename
 */
class JanusServiceRegistrySSPIntegrationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        /** @var SimpleSAML_Configuration $legacyJanusConfig */
        $legacyJanusConfig = $container->get('janus_config');

        $this->setDbParameters($legacyJanusConfig->getArray('store'), $container);

        $this->setParameters(
            'memcache.',
            array(
                'server_groups' => array()
            ),
            $container
        );

        // @todo move memcache config to janus config instead of using values from simplesamlphp
        try {
            /** @var SimpleSAML_Configuration $legacySspConfig */
            $legacySspConfig = $container->get('ssp_config');
            $memcacheConfig = $legacySspConfig->getArray('memcache_store.servers', false);
        } catch (\Exception $ex) {
            // No config (this happens when janus is running in stand alone mode) and simplesamlphp
            // resides in vendor dir
            $memcacheConfig = array();
        }

        $this->setMemcacheParameters($memcacheConfig, $container);
    }

    /**
     * Sets parameters for Database based on config.
     *
     * @param array $dbConfig
     * @param ContainerBuilder $container
     */
    private function setDbParameters(array $dbConfig, ContainerBuilder $container)
    {
        $dbConfigParser = new DbConfigParser();
        // Parse db config
        $this->setParameters(
            'database_',
            $dbConfigParser->parse($dbConfig),
            $container
        );
    }

    /**
     * Sets parameters for memcache based on config.
     *
     * @param array $memcacheConfig
     * @param ContainerBuilder $container
     */
    private function setMemcacheParameters(array $memcacheConfig, ContainerBuilder $container) {
        $memcacheConfigParser = new MemcacheConfigParser();

        $serverGroups = array();
        if (!empty($memcacheConfig)) {
            $serverGroups = $memcacheConfigParser->parse($memcacheConfig);
        }

        $this->setParameters(
            'memcache.',
            array(
                'server_groups' => $serverGroups
            ),
            $container
        );
    }

    /**
     * @param string $prefix
     * @param array $parameters
     * @param ContainerBuilder $container
     */
    private function setParameters($prefix, array $parameters, ContainerBuilder $container)
    {
        foreach ($parameters as $name => $value) {
            $container->setParameter($prefix . $name, $value);
        }
    }
}
