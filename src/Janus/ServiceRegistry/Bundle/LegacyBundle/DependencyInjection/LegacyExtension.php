<?php

namespace Janus\ServiceRegistry\Bundle\LegacyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * @todo find out why this class (only) works when using the full name class and short name filename
 */
class JanusServiceRegistryLegacyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = __DIR__ . '/../Resources/config';
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'janus_service_registry_legacy';
    }
}
