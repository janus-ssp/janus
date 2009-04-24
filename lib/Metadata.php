<?php
/**
 * Contains Metadata class for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Class implementing JANUS metadata.
 *
 * Metadata class that extends the Database class implementing the basic 
 * functionality used for creating metadata entries.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_Metadata extends sspmod_janus_Database {

	/**
	 * Entity id
	 * @var string
	 */
	private $_entityid;

	/**
	 * Revision id
	 * @var int
	 */
	private $_revisionid;

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
	private $_modified = FALSE;

	/**
	 * sspmod_janus_Metadata
	 *
	 * Class constructor that parses the configuration.
	 *
	 * @param SimpleSAML_Configuration &$config Configuration for the 
	 */
	public function __construct($config) {
		parent::__construct($config);
	}

	/**
	 * Load metadata
	 *
	 * Load the metadata from database. The entity id, revision id and the key 
	 * must be set.
	 *
	 * @return PDOStatement|FALSE The satatement or FALSE on error
	 */
	public function load() {
		
		if(empty($this->_entityid) || is_null($this->_revisionid) || empty($this->_key)) {
			SimpleSAML_Logger::error('JANUS:Metadata:load - entityid and revisionid needs to be set.');
			return FALSE;
		}

		$st = $this->execute(
			'SELECT * FROM '. self::$prefix .'__metadata WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;', 
			array($this->_entityid, $this->_revisionid, $this->_key)
		);
		if($st === FALSE) {
			return FALSE;
		}

		while($row = $st->fetchAll(PDO::FETCH_ASSOC)) {
			$this->_value = $row['0']['value'];
		
			$this->_modified = FALSE;
		}

		return $st;
	}

	/**
	 * Save metadata
	 *
	 * Save the metadata to database. Entity id and key must be set. Nothing is 
	 * written to database, i f no modifications have been made.
	 *
	 * @return PDOStatement|FALSE The statement or FALSE on error.
	 */
	public function save() {
		if(!$this->_modified) {
			return TRUE;
		}
		if(!empty($this->_entityid) && !empty($this->_key)) {
			$st = $this->execute('
				INSERT INTO '. self::$prefix .'__metadata (`entityid`, `revisionid`, `key`, `value`, `created`, `ip`) 
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
	 * Set entity id
	 *
	 * Set the entity id.
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
	 * Set the revision id.
	 *
	 * @param int $revisionid Revision id
	 */
	public function setRevisionid($revisionid) {
		assert('ctype_digit((string) $revisionid);');

		$this->_revisionid = $revisionid;

		$this->_modified = TRUE;
	}

	/**
	 * Set key
	 *
	 * Set the metadata key.
	 *
	 * @param string $key Metadata key
	 */
	public function setKey($key) {
		assert('is_string($key)');

		$this->_key = $key;

		$this->_modified = TRUE;
	}
	
	/**
	 * Set value
	 *
	 * Set the metadata value.
	 *
	 * @param string Metadata value
	 */
	public function setValue($value) {
		assert('is_string($value)');

		$this->_value = $value;

		$this->_modified = TRUE;
	}

	/**
	 * Get entity id
	 *
	 * Get the entity id.
	 *
	 * @return string Entity id
	 */
	public function getEntityid() {
		return $this->_entityid;
	}

	/**
	 * Get revision id
	 *
	 * Get the revision id.
	 *
	 * @return int Revision id
	 */
	public function getRevisionid() {
		return $this->_revisionid;
	}

	/**
	 * Get key
	 *
	 * Get the metadata key.
	 *
	 * @return string Metadata key
	 */
	public function getKey() {
		return $this->_key;
	}

	/**
	 * Get value
	 *
	 * Get the metadata value.
	 *
	 * @return string Metadata value
	 */
	public function getValue() {
		return $this->_value;
	}
}
?>
