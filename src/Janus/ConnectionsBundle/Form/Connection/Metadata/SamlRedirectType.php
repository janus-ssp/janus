<?php

namespace Janus\ConnectionsBundle\Form\Connection\Metadata;

use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer;
use sspmod_janus_Model_Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SamlRedirectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sign', 'checkbox');
        $builder->add('validate', 'checkbox');
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
        return 'samlRedirectType';
    }
}
