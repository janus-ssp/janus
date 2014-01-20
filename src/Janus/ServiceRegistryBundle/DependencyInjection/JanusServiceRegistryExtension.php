<?php

namespace Janus\ServiceRegistryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class JanusServiceRegistryExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = __DIR__ . '/../Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $env = $container->getParameter("kernel.environment");
        $loader->load($configDir . '/parameters_' . $env . '.yml');
        $loader = new XmlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.xml');
    }

    public function getAlias()
    {
        return 'janus_service_registry';
    }
}
