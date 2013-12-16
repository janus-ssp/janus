<?php
namespace Janus\Model\Connection\Metadata;

use Janus\ConnectionsBundle\Form\MetadataType;

class FieldConfig
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
    private $options;

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

    public function __construct(
        $type,
        $isRequired,
        array $supportedKeys = array(),
        array $options = array(),
        $defaultValue = null,
        $validationType = null
    )
    {
        $this->setType($type);
        $this->isRequired = $isRequired;
        $this->supportedKeys = $supportedKeys;
        $this->options = $options;
        $this->defaultValue = $defaultValue;
        $this->validationType = $validationType;
    }

    /**
     * Adds children for this field
     *
     * @param array $children
     */
    public function addChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * Sets type and converts it to a specific object if necessary
     *
     * @param string $type
     */
    private function setType($type)
    {
        if ($type == 'boolean') {
            $this->type = 'checkbox'; //new BooleanType($this);
        } else {
            $this->type = $type;
        }
    }

    /**
     * Converts Janus metadata field config into FieldConfig.
     *
     * @param array $config
     * @return array
     */
    public static function createFromSimpleSamlPhpConfig($config)
    {
        $type = 'text';
        if (isset($config['type'])) {
            if ($config['type'] == 'boolean') {
                $type = 'boolean';
            }
        }

        $isRequired = false;
        if (isset($config['required'])) {
            if ($config['required'] === true) {
                $isRequired = true;
            }
        }

        $supportedKeys = array();
        if (isset($config['supported'])) {
            if (is_array($config['supported'])) {
                $supportedKeys = $config['supported'];
            }
        }

        $options = array();
        if (isset($config['select_values'])) {
            if (is_array($config['select_values'])) {
                $options = $config['select_values'];
            }
        }

        $defaultValue = null;
        if (isset($config['default'])) {
            $defaultValue = $config['default'];
        }

        $validationType = null;
        if (isset($config['validate'])) {
            $validationType = $config['validate'];
        }

        return new self($type, $isRequired, $supportedKeys, $options, $defaultValue, $validationType);
    }

    /**
     * @param $fieldsConfigNested
     */
    public function addChildConfig(array $fieldsConfigNested)
    {
        foreach ($fieldsConfigNested as $field => $fieldInfo) {
            /**
             * Parse multiple field notation
             *
             * Multiple fields are either denoted as name:#:en
             * or contacts:0:contactType
             *
             * Since the supported values are known only the first config will be parsed
             */
            $fieldConfig = $this->findConfig($fieldInfo);
            if ($fieldConfig) {
                $this->children[$field] = self::createFromSimpleSamlPhpConfig($fieldConfig);
            } elseif (is_array($fieldInfo)) {
                $keys = implode(':', array_keys($fieldInfo));
                $isCollection = !preg_match('/[^#\d:]/', $keys);
                if ($isCollection) {
                    $firstConfig = reset($fieldInfo);
                    $fieldConfig = $this->findConfig($firstConfig);
                    if ($fieldConfig) {
                        $this->children[$field] = new FieldConfigCollection(
                            self::createFromSimpleSamlPhpConfig($firstConfig)
                        );
                    } else {
                        $group = new FieldConfig('group', false);
                        $group->addChildConfig($firstConfig);
                        $this->children[$field] = new FieldConfigCollection($group);
                    }
                } else {
                    // Group
                    $this->children[$field] = new FieldConfig('group', false);
                    $this->children[$field]->addChildConfig($fieldInfo);
                }
            }
        }
    }

    /**
     * Extracts field config if exists
     *
     * @param array $fieldInfo
     * @return mixed
     */
    private function findConfig(array $fieldInfo)
    {
        if (isset($fieldInfo[ConfigFieldsParser::CONFIG_TOKEN])) {
            return $fieldInfo[ConfigFieldsParser::CONFIG_TOKEN];
        }
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
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
    public function getOptions()
    {
        return $this->options;
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
        // @todo do this neater
        if ($this->type == 'group') {
            $this->type = new MetadataType($this->getChildren());
        }

        return $this->type;
    }

    /**
     * @return string
     */
    public function getValidationType()
    {
        return $this->validationType;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
}