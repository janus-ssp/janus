<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat\MemcacheConfigParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat\DbConfigParser;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection\Configuration;

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

        // Parse db config
        $dbConfigParser = new DbConfigParser();
        $this->setParameters(
            'database_',
            $dbConfigParser->parse($legacyJanusConfig->getArray('store')),
            $container
        );

        /** @var SimpleSAML_Configuration $legacyJanusConfig */
        $legacySspConfig = $container->get('ssp_config');

        // Parse memcache config (if set)
        $memcacheConfig = $legacySspConfig->getArray('memcache_store.servers', false);
        if (!empty($memcacheConfig)) {
            $memcacheConfigParser = new MemcacheConfigParser();
            $this->setParameters(
                'memcache.',
                array(
                    'server_group' => $memcacheConfigParser->parse($memcacheConfig)
                ),
                $container
            );
        }
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