<?php
/**
 * Contains Attribute class for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Class implementing JANUS attribute.
 *
 * Attribute class that extends the Database class implementing the basic 
 * functionality used for creating attribute entries.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_Attribute extends sspmod_janus_Database {

	/**
	 * Entity id of the entity that the attribute is connected to.
	 * @var string
	 */
	private $_entityid;
	
	/**
	 * The revision of the attribute.
	 * @var int
	 */
	private $_revisionid;
	
	/**
	 * Attribute key
	 * @var string
	 */
	private $_key;
	
	/**
	 * Attribute value
	 * @var string
	 */
	private $_value;

	/**
	 * Modify status for the attribute
	 * @var bool
	 */
	private $_modified = FALSE;

	/**
	 * sspmod_janus_Attribute
	 *
	 * Class constructor that parses the configuration.
	 *
	 * @param SimpleSAML_Configuration &$config Configuration for database
	 */
	public function __construct(&$config) {
		parent::__construct($config);
	}

	/**
	 * Load attribute.
	 *
	 * Loads the attribute from database. The entity id,  revision id and key 
	 * needs to be set. 
	 *
	 * @return PDOStatement|FALSE The statement og FALSE on error.
	 * @use PHP_MANUAL#
	 */
	public function load() {
		
		// Check that the entityid, revisionid and key is set
		if(empty($this->_entityid) || is_null($this->_revisionid) || empty($this->_key)) {
			SimpleSAML_Logger::error('JANUS:Metadata:load - entityid and revisionid needs to be set.');
			return FALSE;
		}

		$st = $this->execute(
			'SELECT * FROM '. self::$prefix .'__attribute WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;', 
			array($this->_entityid, $this->_revisionid, $this->_key)
		);
		if($st === FALSE) {
			return FALSE;
		}

		// Fetch the valu and save it in the object
		while($row = $st->fetchAll(PDO::FETCH_ASSOC)) {
			$this->_value = $row['0']['value'];
		
			$this->_modified = FALSE;
		}

		return $st;
	}

	/**
	 * Save attribute.
	 *
	 * Saves the attribute to database. If the attribute is not modified nothing 
	 * is send to the database. Entity id and key needs to be set before hand.
	 *
	 * @return bool TRUE on success and FALSE on error
	 */
	public function save() {
		// Has the sttribute been modified?
		if(!$this->_modified) {
			return TRUE;
		}

		// Is entityid and key set
		if(!empty($this->_entityid) && !empty($this->_key)) {
			$st = $this->execute('
				INSERT INTO '. self::$prefix .'__attribute (`entityid`, `revisionid`, `key`, `value`, `created`, `ip`) 
				VALUES 
				(?, ?, ? ,?, ?, ?);',
				array(
					$this->_entityid, 
					$this->_revisionid, 
					$this->_key, 
					$this->_value, 
					date('c'), 
					$_SERVER['REMOTE_ADDR']
				)
			);

			if($st === FALSE) {
				return FALSE;
			}

		} else {
			return FALSE;
		}

		return $st;
	}

	/**
	 * Set entity id.
	 *
	 * Set the entity id. The entity id needs to be set before calling {@link 
	 * load()}.
	 *
	 * @param string $entityid Entity id
	 */
	public function setEntityid($entityid) {
		assert('is_string($entityid)');

		$this->_entityid = $entityid;

		$this->_modified = TRUE;
	}
	
	/**
	 * Set revision id
	 *
	 * Set the revision id. The revision id needs to be set before calling 
	 * {@link load()}.
	 *
	 * @param int $revisionid The revision id
	 */
	public function setRevisionid($revisionid) {
		assert('ctype_digit((string) $revisionid);');

		$this->_revisionid = $revisionid;

		$this->_modified = TRUE;
	}

	/**
	 * Set key.
	 *
	 * Set the attribute key. The key needs to be set before calling {load()}.
	 *
	 * @param string $key The attribute key
	 */
	public function setKey($key) {
		assert('is_string($key)');

		$this->_key = $key;

		$this->_modified = TRUE;
	}

	/**
	 * Set value
	 *
	 * Set the attribute value.
	 *
	 * @param string $value The attribute value
	 */
	public function setValue($value) {
		assert('is_string($value)');

		$this->_value = $value;

		$this->_modified = TRUE;
	}

	/**
	 * Get entity id.
	 *
	 * Get the entity id.
	 *
	 * @return string The entity id
	 */
	public function getEntityid() {
		return $this->_entityid;
	}

	/**
	 * Get revision id
	 *
	 * Get the revision id
	 *
	 * @return int Revision id
	 */
	public function getRevisionid() {
		return $this->_revisionid;
	}

	/**
	 * Get key
	 *
	 * Get the attribute key
	 *
	 * @return string Attribute key
	 */
	public function getKey() {
		return $this->_key;
	}

	/**
	 * Get value
	 *
	 * Get the attribute value
	 *
	 * @return string Attribute value 
	 */
	public function getValue() {
		return $this->_value;
	}
}
?>
