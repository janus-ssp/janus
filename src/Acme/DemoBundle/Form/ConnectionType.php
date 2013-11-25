<?php

namespace Acme\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', 'textarea');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => 'Acme\DemoBundle\Model\Connection',
            'intention'          => 'connection',
            'translation_domain' => 'AcmeDemoBundle'
        ));
    }

    public function getName()
    {
        return 'connection';
    }
}
