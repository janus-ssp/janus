<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     *
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('janus');
        $this->addStoreSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add Store section to configuration tree
     *
     * @param TreeBuilder ArrayNodeDefinition $janusConfig
     */
    private function addStoreSection(ArrayNodeDefinition $janusConfig)
    {
        $janusConfig
                ->children()
                    ->arrayNode('store')
                        ->children()
                            ->scalarNode('dsn')
                            ->end()
                            ->scalarNode('username')->defaultValue('janus')->end()
                            ->scalarNode('password')->defaultValue('janus_password')->end()
                            ->scalarNode('prefix')->defaultValue('janus__')->end()
        ;
    }
}