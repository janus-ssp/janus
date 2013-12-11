<?php

namespace Janus\ConnectionsBundle\Form\Connection\Metadata;

use sspmod_janus_Model_Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslatableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // @todo make variable
        $builder->add('en', 'text');
        $builder->add('nl', 'text');
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
        return 'translatableType';
    }
}
