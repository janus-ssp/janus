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

        $nodeBuilder = $rootNode->children();

        $nodeBuilder
            ->arrayNode('defaultusertype', 'technical');

        $nodeBuilder
            ->arrayNode('enable')->prototype('boolean');

        $nodeBuilder->arrayNode('entity')->children()
            ->scalarNode('prettyname')->end()
            ->booleanNode('useblacklist')->end()
            ->booleanNode('usewhitelist')->end()
            ->booleanNode('validateEntityId')->end();

        $nodeBuilder
            ->arrayNode('encryption')->children()
            ->booleanNode('enable')->defaultValue(false);

        $nodeBuilder
            ->arrayNode('md')
            ->prototype('array')
            ->prototype('scalar');

        $nodeBuilder
            ->arrayNode('user')->children()
            ->booleanNode('autocreate');

        $nodeBuilder
            ->scalarNode('useridattr');

        $nodeBuilder
            ->arrayNode('usertypes')
            ->prototype('scalar');

        $this->addAccessSection($nodeBuilder);
        $this->addAdminSection($nodeBuilder);
        $this->addAuthSection($nodeBuilder);
        $this->addAttributesSection($nodeBuilder);
        $this->addCaBundleFileSection($nodeBuilder);
        $this->addCertSection($nodeBuilder);
        $this->addDashboardSection($nodeBuilder);
        $this->addMdExportSection($nodeBuilder);
        $this->addMessengerSection($nodeBuilder);
        $this->addMetadatafieldsSection($nodeBuilder);
        $this->addMetadataCronSection($nodeBuilder);
        $this->addWorkflowSections($nodeBuilder);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addAccessSection(NodeBuilder $nodeBuilder)
    {
        $accessChildren = $nodeBuilder
            ->arrayNode('access')->children();

        $rights = array(
            'changemanipulation',
            'changearp',
            'editarp',
            'addarp',
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
                ->arrayNode($right)->children()
                    ->booleanNode('default')->defaultValue(false)->end()
                    ->arrayNode('workflow_states')
                        ->prototype('array')
                            ->prototype('scalar')
            ;
        }
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addAdminSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('admin')->children()
                ->scalarNode('name')->defaultValue('JANUS admin')->end()
                ->scalarNode('email')->end();
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addAuthSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('auth');
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addAttributesSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('attributes')
                ->prototype('array')
                    ->prototype('scalar');
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addCaBundleFileSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('ca_bundle_file');
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addCertSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('cert')->children()
                ->arrayNode('allowed')->children()
                    ->scalarNode('warnings')->end()
                ->end()->end()
                ->arrayNode('strict')->children()
                    ->booleanNode('validation');
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
    private function addDashboardSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('dashboard')->children()
                ->arrayNode('inbox')->children()
                    ->scalarNode('paginate_by')->defaultValue(20);
    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
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

    /**
     * @param NodeBuilder $nodeBuilder
     */
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

    /**
     * @param NodeBuilder $nodeBuilder
     */
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
     * @param NodeBuilder $nodeBuilder
     */
    private function addMetadataCronSection(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('metadata_refresh_cron_tags')
            ->prototype('scalar')
            ->defaultValue('hourly');

        $nodeBuilder
            ->arrayNode('validate_entity_certificate_cron_tags')
            ->prototype('scalar')
            ->defaultValue('daily');

        $nodeBuilder
            ->arrayNode('validate_entity_endpoints_cron_tags')
            ->prototype('scalar')
            ->defaultValue('daily');

    }

    /**
     * @param NodeBuilder $nodeBuilder
     */
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

        $nodeBuilder
            ->arrayNode('workflowstates')
                ->prototype('array')->children()
                    ->arrayNode('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('description')
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('isDeployable')->end()
                    ->scalarNode('textColor')
        ;

    }
}
