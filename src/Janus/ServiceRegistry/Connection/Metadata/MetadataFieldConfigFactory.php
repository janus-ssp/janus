<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

class MetadataFieldConfigFactory
{
    /**
     * Converts Janus metadata field config into MetadataFieldConfig.
     *
     * @param array $config
     * @return MetadataFieldConfig
     */
    public function createFromSimpleSamlPhpConfig($config)
    {
        return new MetadataFieldConfig(
            $this->getTypeFromConfig($config),
            $this->getIsRequiredFromConfig($config),
            $this->getSupportedKeysFromConfig($config),
            $this->getChoicesFromConfig($config),
            $this->getDefaultValueFromConfig($config),
            $this->getValidationTypeFromConfig($config)
        );
    }

    /**
     * Tries to find supported keys in config
     *
     * @param array $config
     * @return array()
     */
    public function getSupportedKeysFromConfig(array $config)
    {
        if (!isset($config['supported'])) {
            return array();
        }

        if (!is_array($config['supported'])) {
            return array();
        }

        return $config['supported'];
    }

    /**
     * @param $config
     * @return array
     */
    protected function getTypeFromConfig($config)
    {
        $defaultType = 'text';

        if (!isset($config['type'])) {
            return $defaultType;
        }

        if ($config['type'] == 'boolean') {
            return 'checkbox';
        }

        if ($config['type'] == 'select') {
            return 'choice';
        }

        return $defaultType;
    }

    /**
     * @param $config
     * @return array
     */
    protected function getIsRequiredFromConfig($config)
    {
        if (!isset($config['required'])) {
            return false;
        }

        if ($config['required'] !== true) {
            return false;
        }

        return true;
    }

    /**
     * @param $config
     * @return array
     */
    protected function getChoicesFromConfig($config)
    {
        if (!isset($config['select_values'])) {
            return array();
        }

        if (!is_array($config['select_values'])) {
            return array();
        }

        $choices = array();
        foreach ($config['select_values'] as $choice) {
            $choices[$choice] = $choice;
        }
        return $choices;
    }

    /**
     * @param $config
     * @return array
     */
    protected function getDefaultValueFromConfig($config)
    {
        $defaultValue = null;

        if (!isset($config['default'])) {
            return $defaultValue;
        }

        return $config['default'];
    }

    /**
     * @param $config
     * @return null
     */
    protected function getValidationTypeFromConfig($config)
    {
        $defaultValidationType = null;

        if (!isset($config['validate'])) {
            return $defaultValidationType;
        }

        return $config['validate'];
    }
}
