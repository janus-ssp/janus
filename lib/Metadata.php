<?php
/**
 * Metadata element
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.0.0
 */
/**
 * Metadata element
 *
 * The class implements basic functionality regarding creating and updating
 * metadata elements.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 */
class sspmod_janus_Metadata extends sspmod_janus_Database
{
    /**
     * EntityRevision id
     * @var int
     */
    protected $_connectionRevisionId;

    /**
     * Metadata key
     * @var string
     */
    protected $_key;

    /**
     * Metadata value
     * @var string
     */
    protected $_value;

    /**
     * Metadata field definition.
     *
     * @var sspmod_janus_Metadatafield
     */
    protected $_definition;

    /**
     * Creates a new instance of matadata
     *
     * @param sspmod_janus_Metadatafield $definition
     * @param string $key
     * @param string $value
     */
    public function __construct(sspmod_janus_Metadatafield $definition, $key, $value)
    {
        $this->_definition = $definition;

        $this->_key = $key;
        $this->castKey();

        $this->_value = $value;
        $this->castValue();
    }

    /**
     * Cast the key to an integer if it is numeric.
     */
    protected function castKey()
    {
        if (is_numeric($this->_key)) {
            $this->_key = (int)$this->_key;
        }
    }

    /**
     * Cast the given value to the appropriate scalar based on the metadata field definition.
     */
    protected function castValue()
    {
        if ($this->_definition->getType() === 'boolean') {
            $this->castValueToBoolean();
            return;
        }

        if (is_numeric($this->_value)) {
            $this->_value = (int)$this->_value;
            return;
        }
    }

    /**
     * Value should be a boolean.
     * Stricter semantics than PHPs native convert to boolean, only '1' is a valid true.
     */
    protected function castValueToBoolean()
    {
        if ($this->_value === '1') {
            $this->_value = true;
            return;
        }

        $this->_value = false;
    }

    /**
     * Set entity id
     *
     * @param string $connectionRevisionId Connection Revision
     *
     * @return void
     */
    public function setConnectionRevisionId($connectionRevisionId)
    {
        assert('ctype_digit($connectionRevisionId)');

        $this->_connectionRevisionId = $connectionRevisionId;
    }


    /**
     * Get metadata key
     *
     * @return string Metadata key
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get metadata value
     *
     * @return string Metadata value
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set metadata value
     *
     * @param string $value Metadata value
     *
     * @return $this
     */
    public function updateValue($value)
    {
        $this->_value = $value;

        return $this;
    }
}