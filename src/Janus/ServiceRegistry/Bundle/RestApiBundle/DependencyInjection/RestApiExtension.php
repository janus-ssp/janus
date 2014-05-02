<?php

namespace Janus\ServiceRegistry\Bundle\RestApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class JanusServiceRegistryRestApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configDir = __DIR__ . '/../Resources/config';

        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'janus_service_registry_rest_api';
    }
}
