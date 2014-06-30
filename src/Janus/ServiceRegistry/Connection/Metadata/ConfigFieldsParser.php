<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\ArrayPathHelper;

/**
 * Parses flat Janus fields config into a hierarchical config usable with symfony form fields.
 */
class ConfigFieldsParser
{
    const CONFIG_TOKEN = '__config';

    /**
     * Parses config into hierarchical structure and sets parsed config.
     *
     * @param array $config
     * @return MetadataFieldConfig
     */
    public function parse(array $config)
    {
        $fieldsConfigNested = $this->convertConfigToNestedArray($config);

        $config = new MetadataFieldConfig('metadata', true);
        $config->addChildConfig($fieldsConfigNested);

        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    private function convertConfigToNestedArray(array $config)
    {
        $arrayPathHelper = new ArrayPathHelper();
        foreach ($config as $field => $fieldConfig) {
            $arrayPathHelper->set($field, array(
                self::CONFIG_TOKEN => $fieldConfig
            ));
        }
        $fieldsConfigNested = $arrayPathHelper->getArray();
        return $fieldsConfigNested;
    }
}
