<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\Connection\NestedCollection;
use Janus\ServiceRegistry\Connection\Metadata\FieldConfig;

use Janus\ServiceRegistry\NestedValueSetter;

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
     * @return FieldConfig
     */
    public function parse(array $config)
    {
        $fieldsConfigNested = array();
        $nestedValueSetter = new NestedValueSetter($fieldsConfigNested, NestedCollection::PATH_SEPARATOR_REGEX);
        foreach ($config as $field => $fieldConfig) {
            $nestedValueSetter->setValue($field, array(
                self::CONFIG_TOKEN => $fieldConfig
            ));
        }

        $config = new FieldConfig('metadata', true);
        $config->addChildConfig($fieldsConfigNested);

        return $config;
    }
}