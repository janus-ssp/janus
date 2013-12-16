<?php

namespace Janus\Model\Connection\Metadata;

use Janus\ConnectionsBundle\Form\MetadataType;

class FieldConfigCollection
{
    /**
     * @var \FieldConfig
     */
    private $fieldConfig;

    /**
     * @param FieldConfig $fieldConfig
     */
    public function __construct(FieldConfig $fieldConfig)
    {
        $this->fieldConfig = $fieldConfig;
    }

    public function getType()
    {
        return $this->fieldConfig->getType();
    }
}