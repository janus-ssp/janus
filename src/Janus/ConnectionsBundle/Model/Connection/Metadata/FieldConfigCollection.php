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
        if ($this->fieldConfig->getType() == 'group') {
            return new MetadataType($this->fieldConfig->getChildren());
        }

        return $this->fieldConfig->getType();
    }
}