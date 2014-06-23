<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection\Metadata;

class MetadataFieldConfigCollection
    implements MetadataFieldConfigInterface
{
    /**
     * @var MetadataFieldConfig
     */
    private $fieldConfig;

    /**
     * @param MetadataFieldConfig $fieldConfig
     */
    public function __construct(MetadataFieldConfig $fieldConfig)
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

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->fieldConfig->getChoices();
    }
}
