<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

/**
 * Class MetadataDefinitionHelper
 */
class MetadataDefinitionHelper
{
    /**
     * @var string
     */
    protected $connectionType;

    /**
     * @var ConfigProxy
     */
    protected $janusConfig;

    /**
     * @param $connectionType
     * @param ConfigProxy $janusConfig
     */
    public function __construct($connectionType, ConfigProxy $janusConfig)
    {
        $this->connectionType = $connectionType;
        $this->janusConfig = $janusConfig;
    }

    /**
     * Given string data and the JANUS configuration attempts to cast data to appropriate PHP data types.
     *
     * @param array $data
     * @return array
     * @throws \RuntimeException
     */
    public function castData(array $data)
    {
        $metadataFields = $this->getMetadataFieldsForType();

        foreach ($data as $fieldName => &$fieldValue) {
            if (!isset($metadataFields[$fieldName])) {
                // Note that if a admin changes fields he / she may deprecate field configurations that still have data.
                // We currently tolerate this in the GUI, we should tolerate it in the API, but we can't do casting.
                continue;
            }

            $fieldType = $metadataFields[$fieldName]['type'];
            if ($fieldType === 'boolean') {
                $fieldValue = (bool) $fieldValue;
            }
        }

        return $data;
    }

    /**
     * Given 2 parts of a metadata field name key (say 'redirect' and 'sign') will find the appropriate separator
     * (either a colon, :, or a dot, .) from the metadata field definitions and join the two.
     *
     * @param string $parentKey
     * @param string $subKey
     * @return string
     * @throws \RuntimeException
     */
    public function joinKeyParts($parentKey, $subKey)
    {
        if (empty($parentKey)) {
            return $subKey;
        }

        $keyNames = array_keys($this->getMetadataFieldsForType());

        foreach ($keyNames as $keyName) {
            if (strpos($keyName, $parentKey) !== 0) {
                continue;
            }

            $joinedWithColon = $parentKey . ':' . $subKey;
            if (strpos($keyName, $joinedWithColon) === 0) {
                return $joinedWithColon;
            }

            $joinedWithDot = $parentKey . '.' . $subKey;
            if (strpos($keyName, $joinedWithDot) === 0) {
                return $joinedWithDot;
            }
        }

        throw new \RuntimeException(
            "Unable to find proper separator for '$parentKey' '$subKey' " .
            "(tried $parentKey:$subKey and $parentKey.$subKey. " .
            "Perhaps the definition is missing?"
        );
    }

    private function getMetadataFieldsForType()
    {
        $metadataFields = $this->janusConfig->getArray('metadatafields.' . $this->connectionType);

        // Inline 'supported'
        $inlineMetadataFields = array();
        foreach ($metadataFields as $fieldName => $fieldConfig) {
            if (!isset($fieldConfig['supported'])) {
                $inlineMetadataFields[$fieldName] = $fieldConfig;
                continue;
            }

            foreach ($fieldConfig['supported'] as $supportedValue) {
                $inlineFieldName = str_replace('#', $supportedValue, $fieldName);
                $inlineMetadataFields[$inlineFieldName] = $fieldConfig;
            }
        }
        return $inlineMetadataFields;
    }
}