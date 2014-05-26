<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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

        $this->addAccessSection($rootNode->children());
        $this->addAdminSection($rootNode->children());
        $this->addAuthSection($rootNode->children());
        $this->addAttributesSection($rootNode->children());
        $this->addCaBundleFileSection($rootNode->children());
        $this->addCertSection($rootNode->children());
        $this->addDashboardSection($rootNode->children());
        $this->addVarious($rootNode->children());
        $this->addStoreSection($rootNode->children());

        return $treeBuilder;
    }

    /**
     * Add Store section to configuration tree
     *
     * @param TreeBuilder NodeBuilder $nodeBuilder
     */
    private function addVarious(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('defaultusertype', 'technical')->end()
            ->arrayNode('enable')->children()->booleanNode('saml20-idp')->end()
            ->booleanNode('saml20-sp')->end()
            ->booleanNode('shib13-idp')->end()
            ->booleanNode('shib13-sp')->end()
            ->arrayNode('encryption')->children()->arrayNode('enable')->end()
            ->arrayNode('entity')->children()->arrayNode('prettyname', NULL)->end()
            ->arrayNode('useblacklist')->end()
            ->arrayNode('usewhitelist')->end()
            ->arrayNode('validateEntityId', true)->end()
            ->arrayNode('export')->children()->arrayNode('entitiesDescriptorName')->end()
            ->arrayNode('metadata_refresh_cron_tags')->end()
            ->arrayNode('validate_entity_certificate_cron_tags')->end()
            ->arrayNode('validate_entity_endpoints_cron_tags')->end();

        $nodeBuilder->arrayNode('notify')
                ->children()
                    ->arrayNode('cert')
                        ->children()
                            ->arrayNode('expiring')
                                ->children()->scalarNode('before');

        $nodeBuilder->arrayNode('meta')->children()->arrayNode('expiring')->children()->scalarNode('before')->end()
        ->end()
        ->end()
            ->arrayNode('language')->children()->arrayNode('available')->end()
            ->arrayNode('md')->children()->arrayNode('mapping')->end()
            ->arrayNode('mdexport')->children()->arrayNode('allowed_mime')->end()
            ->arrayNode('default_options')->end()
            ->arrayNode('feeds')->end()
            ->arrayNode('postprocessor')->end()
            ->arrayNode('messenger')->children()->arrayNode('default')->end()
            ->arrayNode('external')->end()
            ->arrayNode('metadatafields')->children()->arrayNode('saml20-idp')->end()
            ->arrayNode('saml20-sp')->end()
            ->arrayNode('uploadpath')->end()
            ->arrayNode('revision')->children()->arrayNode('notes')->children()->booleanNode('required')->defaultValue(false)->end()
            ->arrayNode('session')->children()->arrayNode('cookie')->children()->arrayNode('name')->end()
            ->arrayNode('technicalcontact_email')->children()->arrayNode('org')->end()
            ->arrayNode('types')->end()
            ->arrayNode('user')->children()->arrayNode('autocreate', false)->end()
            ->arrayNode('useridattr')->end()
            ->arrayNode('usertypes')->end()
            ->arrayNode('workflow_states')->end()
            ->arrayNode('workflowstate')->children()->arrayNode('default')->defaultValue('testaccepted')->end();
    }

    private function addAccessSection(NodeBuilder $nodeBuilder)
    {
        // Example role:
//        'role'
//        'secretariat',
//        'operations',
//        'all'
//    ),

        $accessChildren = $nodeBuilder
            ->arrayNode('access')
                ->children();


        $rights = array(
            'changeentitytype',
            'exportmetadata',
            'blockremoteentity',
            'changeworkflow',
            'changeentityid',
            'addmetadata',
            'deletemetadata',
            'modifymetadata',
            'importmetadata',
            'validatemetadata',
            'entityhistory',
            'disableconsent',
            'createnewentity',
            'showsubscriptions',
            'addsubscriptions',
            'editsubscriptions',
            'deletesubscriptions',
            'exportallentities',
            'arpeditor',
            'federationtab',
            'admintab',
            'adminusertab',
            'allentities',
            'experimental'
        );

        foreach ($rights as $right) {
            $accessChildren
                ->arrayNode($right)
                    ->children()
                        ->booleanNode('default')->defaultValue(false)->end()
                        ->arrayNode('workflow_states')
                            ->prototype('array')
                                ->prototype('scalar')
            ;
        }
    }

    private function addAdminSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('admin')
                ->children()
                    ->scalarNode('name')->defaultValue('JANUS admin')->end()
                    ->scalarNode('email')->end();
    }

    private function addAuthSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('auth');
    }

    private function addAttributesSection(NodeBuilder $nodeBuilder)
    {
        // Attribute example
//        (
//        'Common name (cn)' => array(
//        'name' => 'cn'
//    ),

        $nodeBuilder
            ->arrayNode('attributes');
    }

    private function addCaBundleFileSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('ca_bundle_file');
    }

    private function addCertSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('cert')
                ->children()
                    ->arrayNode('allowed')
                        ->children()
                            ->scalarNode('warnings')->end()
                        ->end()
                    ->end()
                    ->arrayNode('strict')
                        ->children()
                            ->booleanNode('validation')->end();
    }

    private function addDashboardSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('dashboard')
                ->children()
                    ->arrayNode('inbox')
                        ->children()
                            ->scalarNode('paginate_by')->defaultValue(20)->end();
    }

    /**
     * Adds Store config.
     *
     * @param NodeBuilder $nodeBuilder
     */
    private function addStoreSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
             ->arrayNode('store')->children()
                ->scalarNode('dsn')->end()
                ->scalarNode('username')->defaultValue('janus')->end()
                ->scalarNode('password')->defaultValue('janus_password')->end()
                ->scalarNode('prefix')->defaultValue('janus__')->end();
    }
}
