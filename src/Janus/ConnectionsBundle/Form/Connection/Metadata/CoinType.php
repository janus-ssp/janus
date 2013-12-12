<?php
namespace Janus\ConnectionsBundle\Form\Connection\Metadata;

use sspmod_janus_Model_Connection;
use Janus\ConnectionsBundle\Form\Extension\Transformer\StringToBooleanTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoinType extends AbstractType
{
    /**
     * @var array
     */
    private $metadataFieldsConfig;

    public function __construct(array $metadataFieldsConfig)
    {
        $this->metadataFieldsConfig = $metadataFieldsConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->metadataFieldsConfig as $field => $fieldConfig) {

        $name = $field;
        $config = $this->getFieldConfig($this->metadataFieldsConfig, $name);

        if ($config['type'] == 'checkbox') {
            $builder->add($name, 'checkbox', array(
                'required' => $config['required']
            ))
                ->addModelTransformer(new StringToBooleanTransformer());
        } else {
            $builder->add($name, 'text', array(
                'required' => $config['required']
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

    /**
     * Converts config field to to a symfony type
     *
     * @param array $config
     * @return string
     */
    private function getFieldConfig(array $janusConfig, $name)
    {
        $config = array(
            'type' => 'text',
            'required' => false
        );

        if (isset($janusConfig[$name]['type'])) {
            if ($janusConfig[$name]['type'] == 'boolean') {
                $config['type'] = 'checkbox';
            }
        }

        if (isset($janusConfig[$name]['required'])) {
            if ($janusConfig[$name]['required']) {
                $config['required'] = true;
            }
        }

        return $config;
    }

    public function getName()
    {
        return 'coinType';
    }
}
