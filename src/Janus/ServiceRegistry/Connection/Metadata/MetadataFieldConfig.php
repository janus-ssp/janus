<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

class MetadataFieldConfig
    implements MetadataFieldConfigInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * @var array
     */
    private $supportedKeys;

    /**
     * @var array
     */
    private $choices;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $validationType;

    /**
     * @var array
     */
    private $children;

    /**
     * @var MetadataFieldConfigFactory
     */
    protected $metadataFieldConfigFactory;

    public function __construct(
        $type,
        $isRequired,
        array $supportedKeys = array(),
        array $choices = array(),
        $defaultValue = null,
        $validationType = null
    )
    {
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->supportedKeys = $supportedKeys;
        $this->choices = $choices;
        $this->defaultValue = $defaultValue;
        $this->validationType = $validationType;
        $this->metadataFieldConfigFactory = new MetadataFieldConfigFactory();
    }

    /**
     * @param $fieldsConfigNested
     */
    public function addChildConfig(array $fieldsConfigNested)
    {
        foreach ($fieldsConfigNested as $fieldName => $fieldInfo) {
            $this->addChildForFieldConfig($fieldName, $fieldInfo);
        }
    }

    /**
     * Parse multiple field notation
     *
     * Multiple fields are either denoted as name:#:en
     * or contacts:0:contactType
     *
     * Since the supported values (0,1,3,... or nl,en,etc) are known only the first config will be parsed.
     *
     * @param $fieldName
     * @param $fieldInfo
     * @return array
     */
    private function addChildForFieldConfig($fieldName, $fieldInfo)
    {
        $fieldConfig = $this->findConfig($fieldInfo);
        if ($fieldConfig) {
            $this->children[$fieldName] = $this->metadataFieldConfigFactory->createFromSimpleSamlPhpConfig($fieldConfig);
            return;
        }

        if (!is_array($fieldInfo)) {
            return;
        }

        $keys         = implode(':', array_keys($fieldInfo));
        $isCollection = !preg_match('/[^#\d:]/', $keys);

        if (!$isCollection) {
            $group = new self('group', false);
            $group->addChildConfig($fieldInfo);
            $this->children[$fieldName] = $group;
            return;
        }

        $firstConfig = reset($fieldInfo);
        $fieldConfig = $this->findConfig($firstConfig);
        if ($fieldConfig) {
            $this->children[$fieldName] = new MetadataFieldConfigCollection(
                $this->metadataFieldConfigFactory->createFromSimpleSamlPhpConfig($fieldConfig)
            );
            return;
        }

        $supportedKeys = $this->findSupportedKeysForGroupCollection($firstConfig);
        // Some fields are defined with a hardcoded index
        if (empty($supportedKeys)) {
            if (!strstr($keys, '#')) {
                $supportedKeys = array(0);
            }
        }
        $group = new MetadataFieldConfig('group', false, $supportedKeys);
        $group->addChildConfig($firstConfig);
        $this->children[$fieldName] = new MetadataFieldConfigCollection($group);
    }

    /**
     * Tries to find the supported keys for a group collection
     *
     * @param array $group
     * @return array
     */
    private function findSupportedKeysForGroupCollection(array $group)
    {
        foreach ($group as $child) {
            $childConfig = $this->findConfig($child);
            $supportedKeys = $this->metadataFieldConfigFactory->getSupportedKeysFromConfig($childConfig);

            if (!empty($supportedKeys)) {
                return $supportedKeys;
            }
        }

        return array();
    }

    /**
     * Extracts field config if exists
     *
     * @param array $fieldInfo
     * @return mixed
     */
    private function findConfig(array $fieldInfo)
    {
        if (!isset($fieldInfo[ConfigFieldsParser::CONFIG_TOKEN])) {
            return null;
        }

        return $fieldInfo[ConfigFieldsParser::CONFIG_TOKEN];
    }

    /**
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @return array
     */
    public function getSupportedKeys()
    {
        return $this->supportedKeys;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
}
