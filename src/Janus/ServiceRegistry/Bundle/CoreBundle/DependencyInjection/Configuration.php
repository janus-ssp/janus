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
        $this->addMdExportSection($rootNode->children());
        $this->addMessengerSection($rootNode->children());
        $this->addMetadatafieldsSection($rootNode->children());
        $this->addStoreSection($rootNode->children());
        $this->addWorkflowSections($rootNode->children());

        return $treeBuilder;
    }

    /**
     * Add Store section to configuration tree
     *
     * @param TreeBuilder NodeBuilder $nodeBuilder
     */
    private function addVarious(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder->scalarNode('cache_dir');
        $nodeBuilder->scalarNode('logs_dir');

        $nodeBuilder->arrayNode('defaultusertype', 'technical');
        $nodeBuilder->arrayNode('enable')->prototype('boolean');
//        $nodeBuilder->arrayNode('encryption')->children()->arrayNode('enable');
        $nodeBuilder->arrayNode('entity')
            ->children()
            ->scalarNode('prettyname')->end()
            ->booleanNode('useblacklist')->end()
            ->booleanNode('usewhitelist')->end()
            ->booleanNode('validateEntityId')->end();
//        $nodeBuilder->arrayNode('export')->children()->arrayNode('entitiesDescriptorName')->end()
//        $nodeBuilder->arrayNode('metadata_refresh_cron_tags')->end()
//        $nodeBuilder->arrayNode('validate_entity_certificate_cron_tags')->end()
//        $nodeBuilder->arrayNode('validate_entity_endpoints_cron_tags')->end();

//        $nodeBuilder->arrayNode('notify')
//                ->children()
//                    ->arrayNode('cert')
//                        ->children()
//                            ->arrayNode('expiring')
//                                ->children()->scalarNode('before');
//
//        $nodeBuilder->arrayNode('meta')->children()->arrayNode('expiring')->children()->scalarNode('before')->end()
//        ->end()
//        ->end()
//        $nodeBuilder->arrayNode('language')->children()->arrayNode('available')->end()
        $nodeBuilder
            ->arrayNode('md')
                ->prototype('array')
                    ->prototype('scalar');

//        $nodeBuilder->arrayNode('revision')->children()->arrayNode('notes')->children()->booleanNode('required')->defaultValue(false)->end()
//        $nodeBuilder->arrayNode('session')->children()->arrayNode('cookie')->children()->arrayNode('name')->end()
//        $nodeBuilder->arrayNode('technicalcontact_email')->children()->arrayNode('org')->end()
//        $nodeBuilder->arrayNode('types')->end()
        $nodeBuilder->arrayNode('user')->children()->booleanNode('autocreate');
        $nodeBuilder->scalarNode('useridattr');
        $nodeBuilder->arrayNode('usertypes')->prototype('scalar');
    }

    private function addAccessSection(NodeBuilder $nodeBuilder)
    {
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
        $nodeBuilder
            ->arrayNode('attributes')
                ->prototype('array')
                    ->prototype('scalar');
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

    private function addMdExportSection(NodeBuilder $nodeBuilder)
    {
        $mdExportBuilder = $nodeBuilder->arrayNode('mdexport')->children();

        // Post processor
        $mdExportBuilder
            ->arrayNode('postprocessor')->children()
            // Filesystem
            ->arrayNode('filesystem')->children()
            ->scalarNode('class')->end()
            ->scalarNode('name')->end()
            ->arrayNode('option')->children()
            ->scalarNode('path')->end()
            ->end()->end()
            ->end()->end()

            // FTP
            ->arrayNode('FTP')->children()
            ->scalarNode('class')->end()
            ->scalarNode('name')->end()
            ->arrayNode('option')->children()
            ->scalarNode('host')->end()
            ->scalarNode('path')->end()
            ->scalarNode('username')->end()
            ->scalarNode('password');

        // Feeds
        $mdExportBuilder
            ->arrayNode('feeds')->children()
            ->arrayNode('prod')->children()
            ->arrayNode('types')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('states')
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode('mime')->end()
            ->arrayNode('exclude')
            ->prototype('scalar')->end()
            ->end()
            ->scalarNode('postprocessor')->end()
            ->scalarNode('entitiesDescriptorName')->end()
            ->scalarNode('filename')->end()
            ->scalarNode('maxCache')->end()
            ->scalarNode('maxDuration')->end()
            ->arrayNode('sign')->children()
            ->booleanNode('enable')->end()
            ->scalarNode('privatekey')->end()
            ->scalarNode('privatekey_pass')->end()
            ->scalarNode('certificate');
        // Allowed mime
        $mdExportBuilder
            ->arrayNode('allowed_mime')
            ->prototype('scalar');

        // Default options
        $mdExportBuilder
            ->arrayNode('default_options')->children()
            ->scalarNode('entitiesDescriptorName')->end()
            ->scalarNode('mime')->end()
            ->scalarNode('maxCache')->end()
            ->scalarNode('maxDuration')->end()
            ->arrayNode('sign')->children()
            ->booleanNode('enable')->end()
            ->scalarNode('privatekey')->end()
            ->scalarNode('privatekey_pass')->end()
            ->scalarNode('certificate');
    }


    private function addMessengerSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('messenger')->children()
                ->scalarNode('default')->defaultValue('INBOX')->end()
                ->arrayNode('external')->children()
                    ->arrayNode('mail')->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('name')->end()
                        ->arrayNode('option')->children()
                            ->scalarNode('headers');
    }

    private function addMetadatafieldsSection(NodeBuilder $nodeBuilder)
    {
        $metadataFields = $nodeBuilder
            ->arrayNode('metadatafields')->children();

        $metadataFields->scalarNode('uploadpath');

        $connectionTypes = array(
            'saml20-idp',
            'saml20-sp',
            'shib13-idp',
        );
        foreach ($connectionTypes as $type) {
            $metadataFields
                // @todo fix this
                ->arrayNode(str_replace('-', '_', $type))
                    ->prototype('array')->children()
                        ->scalarNode('default')->end()
                        ->booleanNode('default_allow')->end()
                        ->scalarNode('filetype')->end()
                        ->scalarNode('maxsize')->end()
                        ->booleanNode('required')->end()
                        ->scalarNode('type')->end()
                        ->arrayNode('select_values')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('supported')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('validate')
            ;
        }
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

    private function addWorkflowSections(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('workflowstate')->children()
                ->scalarNode('default')->defaultValue('testaccepted');

        $nodeBuilder
            ->arrayNode('workflow')
            ->prototype('array')
            ->prototype('array')->children()
            ->arrayNode('role')
            ->prototype('scalar');
    }
}
