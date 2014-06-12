<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection;

use Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer\DotToUnderscoreTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class MetadataType
 * @package Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection
 */
class ArpAttributesType extends AbstractType
{
    /**
     * @var \SimpleSAML_Configuration
     */
    protected $janusConfiguration;

    public function __construct(\SimpleSAML_Configuration $janusConfiguration)
    {
        $this->janusConfiguration = $janusConfiguration;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributesConfig = $this->janusConfiguration->getArray('attributes');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $newData = array();

            $submittedData = $event->getData();
            foreach ($submittedData as $attributeName => $attributeValues) {
                $newData[str_replace('.', '_', $attributeName)] = $attributeValues;
            }

            $event->setData($newData);
        });
        $builder->addModelTransformer(new DotToUnderscoreTransformer());
        $builder->addViewTransformer(new DotToUnderscoreTransformer(true));

        foreach ($attributesConfig as $attributeConfig) {
            if (isset($attributeConfig['specify_values']) && $attributeConfig['specify_values']) {
                $builder->add(
                    str_replace('.', '_', $attributeConfig['name']),
                    'collection',
                    array(
                        'type' => 'text',
                        'options' => array(
                            'data' => '*',
                        ),
                        'allow_add' => true,
                        'allow_delete' => true,
                    )
                );
            }
            else {
                $builder->add(
                    str_replace('.', '_', $attributeConfig['name']),
                    'collection',
                    array(
                        'type' => 'text',
                        'options' => array(
                            'data' => '*'
                        ),
                        'constraints' => array(
                            new Count(array('min'=> 0, 'max'=>1))
                        ),
                        'allow_add' => true,
                        'allow_delete' => true,
                    )
                );
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'intention' => 'connection',
            'translation_domain' => 'JanusServiceRegistryBundle',
            'extra_fields_message' => 'This form should not contain these extra fields: "{{ extra_fields }}"',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "arpAttributes";
    }
}