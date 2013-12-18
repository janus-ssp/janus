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

    /**
     * Returns keys that are supported for this collection.
     *
     * This can be a numbered list like 0,1,2 but also en,nl etc.
     *
     * @return array
     */
    public function getSupportedKeys()
    {
        return $this->fieldConfig->getSupportedKeys();
    }
}