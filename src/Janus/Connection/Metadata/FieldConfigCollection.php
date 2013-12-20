<?php

namespace Janus\Connection\Metadata;

class FieldConfigCollection
    implements FieldConfigInterface
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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->fieldConfig->getType();
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->fieldConfig->getChildren();
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