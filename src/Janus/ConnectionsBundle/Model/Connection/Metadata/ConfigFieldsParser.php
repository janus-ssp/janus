<?php
namespace Janus\Model\Connection\Metadata;

use Janus\ConnectionsBundle\Form\Connection\Metadata\GroupType;
use Janus\Model\Connection\Metadata\FieldConfig;

use Janus\ConnectionsBundle\Model\NestedValueSetter;

/**
 * Parses flat Janus fields config into a hierarchical config usable with symfony form fields.
 */
class ConfigFieldsParser
{
    const CONFIG_TOKEN = '__config';

    const PATH_SEPARATOR_REGEX = '[.:]';

    /**
     * Parses config into hierarchical structure and sets parsed config.
     *
     * @param array $config
     * @return FieldConfig
     */
    public function parse(array $config)
    {
        $fieldsConfigNested = array();
        $nestedValueSetter = new NestedValueSetter($fieldsConfigNested, self::PATH_SEPARATOR_REGEX);
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