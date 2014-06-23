<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection;

use Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer\JanusStringBooleanTransformer;
use Janus\ServiceRegistry\Connection\Metadata\ConfigFieldsParser;
use Janus\ServiceRegistry\Connection\Metadata\MetadataFieldConfig;
use Janus\ServiceRegistry\Connection\Metadata\MetadataFieldConfigCollection;
use Janus\ServiceRegistry\Connection\Metadata\MetadataFieldConfigInterface;
use Janus\ServiceRegistry\Entity\Connection;

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
        foreach ($this->fieldsConfig as $name => $fieldInfo) {
            if ($fieldInfo instanceof MetadataFieldConfigCollection) {
                // Add a collection of fields or field groups
                $type = $this->createType($fieldInfo);
                $supportedKeys = implode(',', $fieldInfo->getSupportedKeys());

                $options = array(
                    'type' => $type,
                    'options' => array(),
                    'attr' => array(
                        'class' => 'field-collection',
                        'data-supported-keys' => $supportedKeys
                    ),
                    'required' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true
                );

                if ($type === 'choice') {
                    $options['options']['choices'] = $fieldInfo->getChoices();
                }

                $builder->add($name, 'collection', $options);
            } elseif ($fieldInfo instanceof MetadataFieldConfig) {
                $type = $this->createType($fieldInfo);
                if ($type instanceof MetadataType) {
                    // Add a group of fields
                    $type = new self($fieldInfo->getChildren());

                    // Add a nested group of fields
                    $builder->add($name, $type, array(
                        'attr' => array(
                            'class' => 'field-group',
                            'required' => true
                        )
                    ));
                }
                else {
                    // Add a single field
                    $options = array(
                        'required' => $fieldInfo->getIsRequired(),
                    );

                    if ($type === 'choice') {
                        $options['choices'] = $fieldInfo->getChoices();
                    }

                    // Requiring checkboxes is not necessary for symfony forms
                    // Since false will be posted when unchecked
                    if ($type === 'checkbox') {
                        $options['required'] = false;
                        $builder->addModelTransformer(new JanusStringBooleanTransformer($name));
                    }

                    // Add a field
                    $builder->add($name, $type, $options);
                }
            } else {
                throw new \InvalidArgumentException(
                    "Unknown field info type '" . is_object($fieldInfo) ? get_class($fieldInfo) : gettype($fieldInfo) . "'"
                );
            }
        }
    }

    /**
     * Creates field from config
     *
     * @param MetadataFieldConfigInterface $fieldInfo
     * @return mixed
     */
    private function createType(MetadataFieldConfigInterface $fieldInfo)
    {
        $type = $fieldInfo->getType();

        // Convert groups to nested metadata fields
        if ($type === 'group') {
            return new self($fieldInfo->getChildren());
        }

        return $type;
    }

    /**
     * @param OptionsResolverInterface $resolver
     *
     * @todo Correct options
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'intention' => 'connection',
            'translation_domain' => 'JanusServiceRegistryBundle',
            'extra_fields_message' => 'This form should not contain these extra fields: "{{ extra_fields }}"',
        ));
    }

    public function getName()
    {
        return 'metadata';
    }
}
