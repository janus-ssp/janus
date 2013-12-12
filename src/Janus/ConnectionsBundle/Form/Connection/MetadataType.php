<?php

namespace Janus\ConnectionsBundle\Form;

use Janus\ConnectionsBundle\Form\Connection\Metadata\TranslatableType;
use Janus\ConnectionsBundle\Form\Extension\Transformer\StringToBooleanTransformer;
use sspmod_janus_Model_Connection;

use Janus\ConnectionsBundle\Form\Connection\Metadata\SamlContactType;
use Janus\ConnectionsBundle\Form\Connection\Metadata\SamlRedirectType;
use Janus\ConnectionsBundle\Form\Connection\Metadata\CoinType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MetadataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $janusConfig = \sspmod_janus_DiContainer::getInstance()->getConfig();
        $idpMetadataFieldsConfig = $janusConfig->getArray('metadatafields.saml20-idp');
        $spMetadataFieldsConfig = $janusConfig->getArray('metadatafields.saml20-sp');

        $metadataFieldsConfig = array_merge($idpMetadataFieldsConfig, $spMetadataFieldsConfig);

        $metadataFieldsConfigNested = array();
        $nestedValueSetter = new \Janus\ConnectionsBundle\Model\NestedValueSetter($metadataFieldsConfigNested, '[.:]');
        foreach ($metadataFieldsConfig as $field => $fieldConfig) {
            $nestedValueSetter->setValue($field, $fieldConfig);
        }

        foreach ($metadataFieldsConfigNested as $field => $fieldConfig) {
            $multiValue = false;
            if (isset($fieldConfig['#'])) {
                $multiValue = true;
                $fieldConfig = $fieldConfig['#'];
            }

            if ($field === 'contacts') {
                $builder->add($field, 'collection', array(
                    'type' => new SamlContactType($fieldConfig),
                    'options' => array(
                        'attr' => array(
                            'class' => 'field-group'
                        ),
                        'allow_add' => true
                    )
                ));
            } elseif ($field === 'coin') {
                $builder->add($field, new CoinType($fieldConfig), array(
                    'attr' => array(
                        'class' => 'field-group'
                    )
                ));
            } elseif ($field === 'redirect') {
                $builder->add($field, new SamlRedirectType());
            } elseif ($multiValue) {
                // @todo improve check on translatable
                $builder->add($field, new TranslatableType(), array(
                    'attr' => array(
                        'class' => 'field-group'
                    )
                ));
            } else {
                $builder->add($field, 'text');
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
