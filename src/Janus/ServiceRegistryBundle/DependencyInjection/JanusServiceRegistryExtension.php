<?php

namespace Janus\ServiceRegistryBundle\DependencyInjection;

use Janus\ServiceRegistry\Compat\DbConfigParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

use Janus\ServiceRegistryBundle\DependencyInjection\Configuration;

use SimpleSAML_Configuration;

class JanusServiceRegistryExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);



        // @todo find out why config is empty
//        if (!empty($config['store'])) {
        $config['store'] = array();
            $this->loadLegacyConfig($config['store'], $container);
//        }

        $config['services'] = array();
        $this->loadServiceConfig($config['services'], $container);
    }

    /**
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadLegacyConfig(array $config, ContainerBuilder $container)
    {
//        $loader =  new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../../../../../simplesamlphp/simplesamlphp/config'));
//        $loader->load('module_janus.php');


//        if (empty($config['default_connection'])) {
//            $keys = array_keys($config['connections']);
//            $config['default_connection'] = reset($keys);
//        }
//        $this->defaultConnection = $config['default_connection'];
//
//        $container->setAlias('database_connection', sprintf('doctrine.dbal.%s_connection', $this->defaultConnection));
//        $container->setAlias('doctrine.dbal.event_manager', new Alias(sprintf('doctrine.dbal.%s_connection.event_manager', $this->defaultConnection), false));
//
//        $container->setParameter('doctrine.dbal.connection_factory.types', $config['types']);
//
//        $connections = array();
//        foreach (array_keys($config['connections']) as $name) {
//            $connections[$name] = sprintf('doctrine.dbal.%s_connection', $name);
//        }
//        $container->setParameter('doctrine.connections', $connections);
//        $container->setParameter('doctrine.default_connection', $this->defaultConnection);
//
//        foreach ($config['connections'] as $name => $connection) {
//            $this->loadDbalConnection($name, $connection, $container);
//        }
    }


    protected function loadServiceConfig(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }


    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }

    public function getAlias()
    {
        return 'janus_service_registry';
    }
}
