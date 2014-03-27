<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type;

use Janus\ServiceRegistry\Entity\Connection;

use Janus\ServiceRegistry\Connection\Metadata\ConfigFieldsParser;
use Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer\Connection\MetadataToNestedCollectionTransformer;
use Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection\MetadataType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionType extends AbstractType
{
    /** @var \Janus\ServiceRegistry\Connection\Metadata\ConfigFieldsParser */
    protected $configFieldsParser;

    /** @var  \SimpleSAML_Configuration */
    protected $janusConfig;

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     */
    public function __construct(\SimpleSAML_Configuration $janusConfig)
    {
        $this->janusConfig = $janusConfig;
        $this->configFieldsParser = new ConfigFieldsParser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('state', 'choice', array(
            'choices' => array(
                'testaccepted' => 'Test Accepted',
                'prodaccepted' => 'Prod Accepted'
            )
        ));
        $builder->add('type', 'choice', array(
            'choices' => array(
                Connection::TYPE_IDP => 'SAML 2.0 Idp',
                Connection::TYPE_SP => 'SAML 2.0 Sp'
            )
        ));
        $builder->add('expirationDate', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataUrl', 'text', array(
            'required' => false
        ));
        $builder->add('metadataValidUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataCacheUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('allowAllEntities', 'checkbox');
        $builder->add('arpAttributes', 'textarea', array(
            'required' => false
        ));
        $builder->add('manipulationCode', 'textarea', array(
            'required' => false
        ));
        $builder->add('parentRevisionNr', 'hidden');
        $builder->add('revisionNote', 'textarea');
        $builder->add('notes', 'textarea', array(
            'required' => false
        ));
        $builder->add('isActive', 'checkbox');

        $this->addMetadataFields($builder, $this->janusConfig, $options['type']);
    }

    /**
     * Adds metadata field with type dependant config
     *
     * @param FormBuilderInterface $builder
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     */
    protected function addMetadataFields(
        FormBuilderInterface $builder,
        \SimpleSAML_Configuration $janusConfig,
        $connectionType)
    {
        $metadataFieldsConfig = $this->getMetadataFieldsConfig($janusConfig, $connectionType);

        $builder->add(
            $builder->create('metadata', new MetadataType($metadataFieldsConfig))
                ->addModelTransformer(new MetadataToNestedCollectionTransformer())
        );
    }

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     * @return array
     */
    protected function getMetadataFieldsConfig(\SimpleSAML_Configuration $janusConfig, $connectionType)
    {
        // Get the configuration for the metadata fields from the Janus configuration
        $janusMetadataFieldsConfig = $this->findJanusMetadataConfig($janusConfig, $connectionType);

        // Convert it to hierarchical structure that we can use to build a form.
        $metadataFieldsConfig = $this->configFieldsParser->parse($janusMetadataFieldsConfig)->getChildren();
        return $metadataFieldsConfig;
    }

    /**
     * @param \SimpleSAML_Configuration $janusConfig
     * @param $connectionType
     * @return mixed
     * @throws \Exception
     */
    protected function findJanusMetadataConfig(\SimpleSAML_Configuration $janusConfig, $connectionType)
    {
        $configKey = "metadatafields.{$connectionType}";
        if (!$janusConfig->hasValue($configKey)) {
            throw new \Exception("No metadatafields config found for type {$connectionType}");
        }

        $metadataFieldsConfig = $janusConfig->getArray($configKey);
        return $metadataFieldsConfig;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Janus\ServiceRegistry\Connection\Dto',
            'intention' => 'connection',
            'translation_domain' => 'JanusServiceRegistryBundle'
        ));
    }

    public function getName()
    {
        return 'connection';
    }
}
