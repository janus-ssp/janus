<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ConnectionReferenceType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'number');
        $builder->add('name', 'hidden');
    }
}