<?php

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

        // Set janus config as parameter so ConfigProxy can use them.
        $container->setParameter('janus_config_values', $config);
    }

    public function getAlias()
    {
        return 'janus_service_registry_core';
    }
}
