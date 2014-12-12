<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

class MetadataTreeFlattener
{
    /**
     * @var MetadataDefinitionHelper
     */
    private $metadataDefinitionHelper;

    /**
     * @param MetadataDefinitionHelper $metadataDefinitionHelper
     */
    public function __construct(MetadataDefinitionHelper $metadataDefinitionHelper)
    {
        $this->metadataDefinitionHelper = $metadataDefinitionHelper;
    }

    /**
     * Turns a nested collection into a flat one.
     *
     * @param array $nestedMetadata
     * @param string $connectionType
     * @param bool $ignoreMissingDefinition
     * @return array
     */
    public function flatten(array $nestedMetadata, $connectionType, $ignoreMissingDefinition = false)
    {
        $flatCollection = array();
        $parentKey = '';
        $this->flattenEntry($flatCollection, $nestedMetadata, $parentKey, $connectionType, $ignoreMissingDefinition);

        return $flatCollection;
    }

    /**
     * Turns a nested entry of a collection into a flat one recursively.
     *
     * @param array $flatCollection
     * @param array $metadata
     * @param string $connectionType
     * @param string $parentKey
     */
    public function flattenEntry(
        array &$flatCollection,
        array $metadata,
        &$parentKey = '',
        $connectionType,
        $ignoreMissingDefinition = false
    )
    {
        foreach ($metadata as $key => $value) {
            $newKey = $this->metadataDefinitionHelper->joinKeyParts($parentKey, $key, $connectionType, $ignoreMissingDefinition);

            if (is_array($value)) {
                $this->flattenEntry($flatCollection, $value, $newKey, $connectionType, $ignoreMissingDefinition);
            } else {
                $flatCollection[$newKey] = $value;
            }
        }
    }
}