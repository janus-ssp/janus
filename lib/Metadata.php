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
     * @param SimpleSAML_Configuration $config Configuration for JANUS
     *
     * @since Class available since Release 1.0.0
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Load metadata
     *
     * Load the metadata from database. The entityrevision id and the key
     * must be set.
     *
     * @return PDOStatement|false The satatement or false on error
     * @since Class available since Release 1.0.0
     */
    public function load()
    {
        if (   empty($this->_connectionRevisionId)
            || empty($this->_key)
        ) {
            SimpleSAML_Logger::error(
                'JANUS:Metadata:load - connectionRevisionId and needs to be set.'
            );
            return false;
        }

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'metadata 
            WHERE `connectionRevisionId` = ? AND `key` = ?;',
            array($this->_connectionRevisionId, $this->_key)
        );
        if ($st === false) {
            return false;
        }

        while ($row = $st->fetchAll(PDO::FETCH_ASSOC)) {
            $this->_value = $row['0']['value'];
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
        return $st;
    }

    /**
     * Save metadata
     *
     * Save the metadata to database. Entity id and key must be set. Nothing is
     * written to database, if no modifications have been made.
     *
     * @return PDOStatement|false The statement or false on error.
     * @throws \Exception
     * @since Class available since Release 1.0.0
     * @todo make this more efficient by not storing each metadata record on it's own
     */
    public function save()
    {
        if ($this->_key == 'coin:oauth:two_legged_allowed') {
            $a = true;
            echo $a;
        }

        if (!$this->_modified) {
            return true;
        }

        // Note that empty values are no longer saved
        if (empty($this->_connectionRevisionId) || empty($this->_key) || $this->_value === '') {
            return false;
        }

        $entityManager = $this->getEntityManager();

        // Get entity revision
        $connectionRevisionId = $this->_connectionRevisionId;
        $connectionRevision = $entityManager->getRepository('sspmod_janus_Model_Connection_Revision')->find($connectionRevisionId);
        if (!$connectionRevision instanceof sspmod_janus_Model_Connection_Revision) {
            throw new \Exception("Entity '{$connectionRevisionId}' not found");
        }

        // Create relation
        $linkedConnectionRelation = new sspmod_janus_Model_Connection_Revision_Metadata(
            $connectionRevision,
            $this->_key,
            $this->_value
        );

        $entityManager->persist($linkedConnectionRelation);
        $entityManager->flush();

        return true;
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
