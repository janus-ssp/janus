<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto;

use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;

class MetadataDtoDisassembler
{
    /**
     * @var MetadataDefinitionHelper
     */
    private $metadataDefinitionHelper;

    /**
     * @var bool
     */
    private $ignoreMissingDefinition;

    /**
     * @param MetadataDefinitionHelper $metadataDefinitionHelper
     * @param bool $ignoreMissingDefinition
     */
    public function __construct(MetadataDefinitionHelper $metadataDefinitionHelper, $ignoreMissingDefinition = false)
    {
        $this->metadataDefinitionHelper = $metadataDefinitionHelper;
        $this->ignoreMissingDefinition = $ignoreMissingDefinition;
    }

    /**
     * Turns a nested collection into a flat one.
     *
     * @param MetadataDto $metadata
     * @return array
     */
    public function disassemble(MetadataDto $metadata)
    {
        $flatCollection = array();
        $parentKey = '';
        $this->flattenEntry($flatCollection, $metadata->getItems(), $parentKey);

        return $flatCollection;
    }

    /**
     * Turns a nested entry of a collection into a flat one recursively.
     *
     * @param array  $flatCollection
     * @param array  $metadata
     * @param string $parentKey
     */
    private function flattenEntry(
        array &$flatCollection,
        array $metadata,
        &$parentKey = ''
    ) {
        foreach ($metadata as $key => $value) {
            $newKey = $this->metadataDefinitionHelper->joinKeyParts($parentKey, $key, $this->ignoreMissingDefinition);

            if (is_array($value)) {
                $this->flattenEntry($flatCollection, $value, $newKey, $this->ignoreMissingDefinition);
            } else {
                $flatCollection[$newKey] = $value;
            }
        }
    }
}