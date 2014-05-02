<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type\Connection;

use Janus\ServiceRegistry\Entity\Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConnectionTypeType extends AbstractType
{
    public function getName()
    {
        return 'type';
    }

    public function getParent()
    {
        return 'choice';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                Connection::TYPE_IDP => 'SAML 2.0 Idp',
                Connection::TYPE_SP => 'SAML 2.0 Sp'
            ),
            'disabled' => true,
            'required' => true,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $hasId = $form->getParent()->getData()->getId();
        if ($hasId) {
            return;
        }

        // This is a new connection, so make the 'type' field available for setting.
        $view->vars['disabled'] = false;
    }
}