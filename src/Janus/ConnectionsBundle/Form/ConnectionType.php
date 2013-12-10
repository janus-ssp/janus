<?php

namespace Janus\ConnectionsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => '\sspmod_janus_Model_Connection_Revision_Dto',
            'intention'          => 'connection',
            'translation_domain' => 'JanusConnectionsBundle'
        ));
    }

    public function getName()
    {
        return 'connection';
    }
}
