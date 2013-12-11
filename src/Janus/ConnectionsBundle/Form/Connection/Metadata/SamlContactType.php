<?php

namespace Janus\ConnectionsBundle\Form\Connection\Metadata;

use sspmod_janus_Model_Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SamlContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactType', 'text');
        $builder->add('givenName', 'text');
        $builder->add('surName', 'text');
        $builder->add('emailAddress', 'text');
        $builder->add('telephoneNumber', 'text');
        $builder->add('company', 'text');
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
        return 'samlContactType';
    }
}
