<?php

namespace Janus\ConnectionsBundle\Form;

use Janus\ConnectionsBundle\Form\Connection\Metadata\TranslatableType;
use sspmod_janus_Model_Connection;

use Janus\ConnectionsBundle\Form\Connection\Metadata\SamlContactType;
use Janus\ConnectionsBundle\Form\Connection\Metadata\SamlRedirectType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MetadataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $janusConfig = \sspmod_janus_DiContainer::getInstance()->getConfig();
        $idpMetadataFieldsConfig = $janusConfig->getArray('metadatafields.saml20-idp');
        $spMetadataFieldsConfig = $janusConfig->getArray('metadatafields.saml20-sp');

        $metadataFieldsConfig = array_merge($idpMetadataFieldsConfig, $spMetadataFieldsConfig);
        foreach ($metadataFieldsConfig as $field => $fieldConfig) {
            $fieldParts = preg_split('/[:.]/', $field);

            if ($fieldParts[0] === 'contacts') {
                $builder->add($fieldParts[0], 'collection', array(
                    'type' => new SamlContactType()
                ));
            } elseif ($fieldParts[0] === 'coin') {
                $builder->add($fieldParts[0], 'collection', array(
                    'type' => 'text'
                ));
            } elseif ($fieldParts[0] === 'redirect') {
                $builder->add($fieldParts[0], new SamlRedirectType());
            } elseif (isset($fieldParts[1]) && $fieldParts[1] == '#' ) {
                $builder->add($fieldParts[0], new TranslatableType(), array(

                ));
            } else {
                $builder->add($fieldParts[0], 'text', array(
                    'required' => false
                ));
            }
        }
//        var_dump($janusConfig);
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
        return 'metadata';
    }
}
