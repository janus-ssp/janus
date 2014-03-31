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
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_Metadata extends sspmod_janus_Database
{
    /**
     * EntityRevision id
     * @var int
     */
    private $_connectionRevisionId;

    /**
     * Metadata key
     * @var string
     */
    private $_key;

    /**
     * Metadata value
     * @var string
     */
    private $_value;

    /**
     * Modify status for the metadata
     * @var bool
     */
    private $_modified = false;

    private $_definition;

    /**
     * Creates a new instanse of matadata
     *
     * @param string $key
     * @param string $value
     *
     * @since Class available since Release 1.0.0
     */
    public function __construct($key, $value)
    {
        $this->setKey($key);
        $this->setConstructValue($value);
    }

    /**
     * @param $value
     */
    private function setConstructValue($value)
    {
        $this->_value = $value;
        if(isset($this->_definition)) {
            switch($this->_definition->type) {
                case 'boolean':
                    if($this->_value == '1') {
                        $this->_value = true;
                    } elseif($this->_value == '') {
                        $this->_value = false;
                    } else {
                        $this->_value = false;
                    }
                    break;
                default:
                    break;
            }
        }
        if(ctype_digit($this->_value)) {
            $this->_value = (int)$this->_value;
        }

        $this->_modified = false;
    }

    /**
     * Set entity id
     *
     * @param string $connectionRevisionId Connection Revision
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setConnectionRevisionId($connectionRevisionId)
    {
        assert('ctype_digit($connectionRevisionId)');

        $this->_connectionRevisionId = $connectionRevisionId;

        $this->_modified = true;
    }

    /**
     * Set metadata key
     *
     * @param string $key Metadata key
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setKey($key)
    {
        assert('is_string($key)');

        if(ctype_digit($key)) {    
            $this->_key = (int)$key;
        } else {
            $this->_key = $key;
        }

        $this->_modified = true;
    }

    /**
     * Set metadata value
     *
     * @param string $value Metadata value
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setValue($value)
    {
        $this->_value = $value;

        $this->_modified = true;
    }


    /**
     * Get metadata key
     *
     * @return string Metadata key
     * @since Class available since Release 1.0.0
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get metadata value
     *
     * @return string Metadata value
     * @since Class available since Release 1.0.0
     */
    public function getValue()
    {
        return $this->_value;
    }

    public function setDefinition(sspmod_janus_Metadatafield $definition) {
        $this->_definition = $definition;
    }

    public function getDefinition() {
        return $this->_definition;
    }
}
?>
