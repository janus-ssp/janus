<?php
/**
 * Options should only be allowed to be set on load and not modified doring 
 * runtime.
 *
 */
class sspmod_janus_Metadatafield
{
    /**
     * Options set for this metadata field
     */
    protected $_options = array();

    /**
     * The metadatafield name
     * @var string
     */
    protected $_name;

    /**
     * List of allowed options for this type
     */
    protected $_allowed_options = array();

    /**
     * Constructor
     *
     * Throw exception if type is not set.
     */
    public function __construct($name, $options)
    {
        if (!isset($name)) {
            throw new Exception('Name of metadata fields not set');
        }

        if (!isset($options['type'])) {
            throw new Exception('Type not set in metadata fields configuration - ' . var_export($options, true));
        }

        $this->_name = $name;
        $this->_initOptions($options);
    }

    /**
     * Set allowed options for this type and set parsewd options
     */
    protected function _initOptions($options)
    {
        // Set allowed options valid for all types
        $this->_allowed_options = array(
            'type',
            'default',
            'default_allow',
            'required',
            'validate',
        );

        switch($options['type']) {
            case 'file':
                $this->_allowed_options[] = 'filetype';
                $this->_allowed_options[] = 'maxsize';
                break;
            case 'select':
                $this->_allowed_options[] = 'select_values';
                break;
            case 'boolean':
            case 'text':
            default:
                break;
        }

        // Set allowed options
        foreach($options AS $name => $value) {
            if (in_array($name, $this->_allowed_options)) {
                $this->_options[$name] = $value;
            } else {
                $trace = debug_backtrace();
                trigger_error(
                    'Option not allowed: ' . $name .
                    ' in ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE);
            }
        }
    }

    /**
     * Only return options set at runtime
     */
    public function __get($name)
    {
        if ($name === 'name') {
            return $this->_name;
        }

        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }

        // Trigger notice if property is not set
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**
     * Only set options defined at setup
     */
    public function __set($name, $value)
    {
        // Only set option if option is allowed
        if (in_array($name, $this->_allowed_options)) {
            $this->_options[$name] = $value;
        }

        // Trigger warning if property is not set
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __set(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_WARNING);
        return null;
    }

    /**
     * Allow for isset call on options
     *
     * This will be usefull when metadata fields are only setup with the parsed 
     * options and all optional options are only set if parsed at setup. Thous 
     * it is up to the caller to decide the action if option is not set.
     */
    public function __isset($name) {
        return isset($this->_options[$name]);
    }

    /**
     * Do not allow unset of options at runtime
     */
    public function __unset($name) {
        // Trigger warning if property is not set
        $trace = debug_backtrace();
        trigger_error(
            'Property can not be unset via __set(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_WARNING);
        return null;
    }
}
