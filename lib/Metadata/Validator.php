<?php

class sspmod_janus_Metadata_Validator
{
    /**
     * @var sspmod_janus_EntityController
     */
    protected $_entityController;

    protected $_errors;
    protected $_warnings;
    protected $_validations;

    protected static $_MISSING_REQUIRED_FIELD = 'Field is required';
    protected static $_DEFAULT_VALUE_NOT_ALLOWED = 'The default value is not allowed';
    protected static $_VALIDATION_OK = 'Ok';

    public function __construct($entityController)
    {
        $this->_entityController = $entityController;
        $this->_errors = array();
        $this->_warnings = array();
        $this->_validations = array();
    }

    public function validate()
    {
        $entityType = $this->_entityController->getEntity()->getType();

        if ($entityType == 'saml20-idp') {
            $idpMetadataConfig = $this->_loadExpandedMetadataConfig(SimpleSAML_Configuration::getConfig('module_janus.php')->getArray('metadatafields.saml20-idp'));
            $this->_validate($idpMetadataConfig);
        } else if ($entityType == 'saml20-sp') {
            $spMetadataConfig = $this->_loadExpandedMetadataConfig(SimpleSAML_Configuration::getConfig('module_janus.php')->getArray('metadatafields.saml20-sp'));
            $this->_validate($spMetadataConfig);
        } else {
            $_errors[] = 'Unknown Entity Type';
        }
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

    public function getValidations()
    {
        return $this->_validations;
    }

    protected function _validate($metadataConfig)
    {
        $entityMetadata = $this->_loadImplodedEntityMetadata($this->_entityController->getMetaArray());

        foreach ($metadataConfig as $k => $v) {
            // Required field
            $errors = array();
            $warnings = array();

            // Missing required field
            if (!array_key_exists($k, $entityMetadata) && $this->_isRequired($v)) {
                $errors[] = self::$_MISSING_REQUIRED_FIELD;

                $this->_setValidations($k, $errors, $warnings);

            } else if (array_key_exists($k, $entityMetadata)) {
                $this->_validateDefaultValue($entityMetadata, $k, $v, $errors, $warnings);
                $this->_validateCustomValidates($entityMetadata, $k, $v, $errors, $warnings);

                $this->_setValidations($k, $errors, $warnings);
            }
            // Do nothing is key is not present and field is not required
        }
    }

    /**
     * Expand the metadata config array, replace AssertionConsumerService:#:Binding
     * with AssertionConsumerService:0:Binding according to the number of possible
     * values specified in the configuration
     *
     * @param  $metadataConfig the config array
     * @return array
     */
    protected function _loadExpandedMetadataConfig($metadataConfig)
    {
        $metadataInfo = array();
        foreach ($metadataConfig as $k => $v) {
            if (!empty($v['supported'])) {
                foreach ($v['supported'] as $supportedValue) {
                    $expandedKey = str_replace('#', $supportedValue, $k);
                    $expandedValue = $v;
                    unset($expandedValue['supported']);
                    $metadataInfo[$expandedKey] = $expandedValue;
                }
            } else if (!array_key_exists('supported', $metadataConfig)) {
                $metadataInfo[$k] = $v;
            }
        }

        return $metadataInfo;
    }

    protected function _loadImplodedEntityMetadata($entityMetadata)
    {
        $implodedEntityMetadata = array();
        $this->_implodeEntityMetadataValues($entityMetadata, '', $implodedEntityMetadata);

        return $implodedEntityMetadata;
    }

    /**
     * Implode the array with metadata so that it is of the following form
     * array ( 'AssertionConsumerService:0:Binding' => 'value');
     *
     * @param  $data the array to implode
     * @param string $implodedKey the imploded key
     * @param  $implodedEntityMetadata the array containing the imploded entries
     * @return void
     */
    protected function _implodeEntityMetadataValues($data, $implodedKey = '', &$implodedEntityMetadata)
    {

       if (is_array($data) && !empty($data)) {
          foreach ($data as $key => $value) {
            $this->_implodeEntityMetadataValues($value, $implodedKey. ':' .$key, $implodedEntityMetadata);
          }
       } else {
          $implodedEntityMetadata[substr($implodedKey, 1)] = $data;
       }
    }

    /**
     * Check if a value is required
     *
     * @param  $metadata The metadata config entry specified in module.janus.config
     * @return bool
     */
    protected function _isRequired($metadata)
    {
        return (array_key_exists('required', $metadata) && $metadata['required'] === true);
    }

    /**
     * Write the validations to the _validations object
     *
     * @param string $key       the current key (metadata entry)
     * @param array  $errors    the array containing errors
     * @param array  $warnings  the array containing warnings
     * @return void
     */
    protected function _setValidations($key, $errors, $warnings)
    {
        if (count($errors) > 0 || count($warnings) > 0) {
            $this->_validations[$key] = array('errors' => $errors, 'warnings' => $warnings);
        } else {
            $this->_validations[$key] = self::$_VALIDATION_OK;
        }
    }

    /**
     * Validate the default default values defined in the module_janus.php config.
     *
     * @param array  $entityMetadata    the metadata to validate
     * @param string $k                 the current key (metadata entry)
     * @param array  $v                 the Janus configuration value array
     * @param array  $errors            the array containing errors
     * @param array  $warnings          the array containing warnings
     * @return void
     */
    protected function _validateDefaultValue($entityMetadata, $k, $v, &$errors, &$warnings)
    {
        if (array_key_exists('default_allow', $v) && $v['default_allow'] === false) {
            // Validations go wrong if the default value equals '' and the entered value is an integer.
            // Hence, transform the default to 'null' if this is the case.
            if (gettype($entityMetadata[$k]) == 'integer' && $v['default'] === '') {
                $default = null;
            } else {
                $default = $v['default'];
            }
            if ($default === $entityMetadata[$k]) {
                $errors[] = self::$_DEFAULT_VALUE_NOT_ALLOWED;
            }
        }
    }

    /**
     * Validate the custom validation functions specified in the Metadata.php file in janus
     * (modules/janus/lib/Validation/Metadata.php).
     *
     * @param array $entityMetadata  the metadata to validate
     * @param string $k              the current key (metadata entry)
     * @param array $v               the Janus configuration value array
     * @param array $errors          the array containing errors
     * @param array $warnings        the array containing warnings
     * @return void
     */
    protected function _validateCustomValidates($entityMetadata, $k, $v, &$errors, &$warnings)
    {
        $functions = array();
        include __DIR__ . '/../Validation/Metadata.php';

        if (array_key_exists('validate', $v) && array_key_exists($v['validate'], $functions)) {
            $validateFunction = $v['validate'];
            $value = $entityMetadata[$k];
            $valid = eval($functions[$validateFunction]['code']);

            if (!$valid) {
                $errors[] = $v['validate_error'];
            }
        }
    }
}
