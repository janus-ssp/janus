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
        $this->add('state', 'text');
        $this->add('type', 'text');
        $this->add('expirationDate', 'text');
        $this->add('metadataUrl', 'text');
        $this->add('metadataValidUntil', 'datetime');
        $this->add('metadataCacheUntil', 'datetime');
        $this->add('allowAllEntities', 'bool');
        $this->add('arpAttributes', 'textArea');
        $this->add('manipulationCode', 'textArea');
        $this->add('parentRevisionNr', 'text');
        $this->add('revisionNote', 'textArea');
        $this->add('notes', 'textArea');
        $this->add('isActive', 'bool');
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
