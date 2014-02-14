<?php

namespace Janus\OauthClientBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class JanusSecurityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load parameters
        $configDir = __DIR__ . '/../Resources/config';
        $loader = new Loader\YamlFileLoader($container, new FileLocator($configDir));
        $env = $container->getParameter("kernel.environment");
        $configFile = $configDir . '/parameters' . '.yml';
        $customConfigFile = $configDir . '/parameters_' . $env . '.yml';
        if (file_exists($customConfigFile)) {
            $configFile = $customConfigFile;
        }
        $loader->load($configFile);


        $loader = new Loader\XmlFileLoader($container, new FileLocator($configDir));
        $loader->load('services.xml');
    }
}
