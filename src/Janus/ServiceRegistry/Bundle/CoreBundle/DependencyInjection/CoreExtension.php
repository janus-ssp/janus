<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection;

use Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat\DbConfigParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * @todo find out why this class (only) works when using the full name class and short name filename
 */
class JanusServiceRegistryCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = __DIR__ . '/../Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $env = $container->getParameter("kernel.environment");
        $configFile = $configDir . '/parameters' . '.yml';
        $customConfigFile = $configDir . '/parameters_' . $env . '.yml';
        if (file_exists($customConfigFile)) {
            $configFile = $customConfigFile;
        }
        $loader->load($configFile);
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.yml');




        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

//        /** @var SimpleSAML_Configuration $legacyJanusConfig */
//        $legacyJanusConfig = $container->get('janus_config');
//        $this->setDbParameters($legacyJanusConfig->getArray('store'), $container);

        $container->get('janus_config')->setConfig($config);
    }

    public function getAlias()
    {
        return 'janus_service_registry_core';
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
