<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

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
        $configFile = $configDir . '/parameters' . '.yml';
        $customConfigFile = $configDir . '/parameters_' . $env . '.yml';
        if (file_exists($customConfigFile)) {
            $configFile = $customConfigFile;
        }
        $loader->load($configFile);
        $loader = new XmlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.xml');
    }

    public function getAlias()
    {
        return 'janus_service_registry';
    }
}
