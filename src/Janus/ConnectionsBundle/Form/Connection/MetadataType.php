<?php

namespace Janus\ConnectionsBundle\Form;

use Janus\ConnectionsBundle\Form\Connection\Metadata\GroupType;
use Janus\Model\Connection\Metadata\ConfigFieldsParser;

use Janus\Model\Connection\Metadata\FieldConfig;
use Janus\Model\Connection\Metadata\FieldConfigCollection;
use sspmod_janus_Model_Connection;

use Janus\ConnectionsBundle\Form\Connection\Metadata\SamlContactType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MetadataType extends AbstractType
{
    /**
     * @var array
     */
    private $fieldsConfig;

    /**
     * @param array $fieldsConfig
     */
    public function __construct(array $fieldsConfig)
    {
        $this->fieldsConfig = $fieldsConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $fieldConfig FieldConfig */
        foreach ($this->fieldsConfig as $name => $fieldConfig) {
            if ($fieldConfig instanceof FieldConfigCollection) {
                $builder->add($name, 'collection', array(
                    'type' => $fieldConfig->getType(),
                    'attr' => array(
                        'class' => 'field-group'
                    )
                ));
            } elseif ($fieldConfig->getType() == 'group') {
                $builder->add($name, new MetadataType($fieldConfig->getChildren()), array(
                    'attr' => array(
                        'class' => 'field-group'
                    )
                ));
            } else {
                $builder->add($name, $fieldConfig->getType(), array(
                    'required' => $fieldConfig->getIsRequired()
                ));
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'intention' => 'connection',
            'translation_domain' => 'JanusConnectionsBundle'
        ));
    }

    public function getName()
    {
        return 'metadata';
    }
}
