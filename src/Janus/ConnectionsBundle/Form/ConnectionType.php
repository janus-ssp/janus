<?php

namespace Janus\ConnectionsBundle\Form;

use sspmod_janus_Model_Connection;

use Janus\ConnectionsBundle\Form\MetadataType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('state', 'choice', array(
            'choices' => array(
                'testaccepted' => 'Test Accepted',
                'prodaccepted' => 'Prod Accepted'
            )
        ));
        $builder->add('type', 'choice', array(
            'choices' => array(
                sspmod_janus_Model_Connection::TYPE_IDP => 'SAML 2.0 Idp',
                sspmod_janus_Model_Connection::TYPE_SP => 'SAML 2.0 Sp'
            )
        ));
        $builder->add('expirationDate', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataUrl', 'text', array(
            'required' => false
        ));
        $builder->add('metadataValidUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('metadataCacheUntil', 'datetime', array(
            'required' => false
        ));
        $builder->add('allowAllEntities', 'checkbox');
        $builder->add('arpAttributes', 'textarea', array(
            'required' => false
        ));
        $builder->add('manipulationCode', 'textarea', array(
            'required' => false
        ));
        $builder->add('parentRevisionNr', 'hidden');
        $builder->add('revisionNote', 'textarea');
        $builder->add('notes', 'textarea', array(
            'required' => false
        ));
        $builder->add('isActive', 'checkbox');

        $builder->add('metadata', new MetadataType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\sspmod_janus_Model_Connection_Revision_Dto',
            'intention' => 'connection',
            'translation_domain' => 'JanusConnectionsBundle'
        ));
    }

    public function getName()
    {
        return 'connection';
    }
}
