<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\DependencyInjection;

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

        $legacyConfig = $container->get('janus_config');

        $dbConfigParser = new DbConfigParser();

        $dbConfig = $dbConfigParser->parse($legacyConfig->getArray('store'));
        $container->setParameter('database_driver', $dbConfig['driver']);
        $container->setParameter('database_host', $dbConfig['host']);
        $container->setParameter('database_port', !empty($dbConfig['port']) ? $dbConfig['port'] : null);
        $container->setParameter('database_name', $dbConfig['dbname']);
        $container->setParameter('database_user', $dbConfig['user']);
        $container->setParameter('database_password', $dbConfig['password']);
        $container->setParameter('database_prefix', $dbConfig['prefix']);
    }
}