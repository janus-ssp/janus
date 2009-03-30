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

	private $_entityid;
	private $_revisionid;
	private $_key;
	private $_value;

	private $modified = FALSE;

	public function __construct($config) {
		parent::__construct($config);
	}

	public function load() {
		
		if(empty($this->_entityid) || empty($this->_revisionid) || empty($this->_key)) {
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
			$this->_value = $row['value'];
		
			$this->_modified = FALSE;
		}

		return $st;
	}

	public function save() {
		if(!$this->_modified) {
			return TRUE;
		}
		if(!empty($this->_entityid) && !empty($this->_key)) {
			// Get next revisionid
			$st = $this->execute(
				'SELECT MAX(`revisionid`) AS maxrevisionid FROM '. self::$prefix .'__metadata WHERE `entityid` = ? AND `key` = ?;',
				array($this->_entityid, $this->_key)
			);

			if($st === FALSE) {
				return FALSE;
			}

			$row = $st->fetchAll(PDO::FETCH_ASSOC);

			if($row[0]['maxrevisionid'] === NULL) {
				$new_revisionid = 0;
			} else {
				$new_revisionid = $row[0]['maxrevisionid'] + 1;
			}

			$st = $this->execute('
				INSERT INTO '. self::$prefix .'__metadata (`entityid`, `revisionid`, `key`, `value`, `created`, `ip`) 
				VALUES 
				(?, ?, ? ,?, ?, ?);',
				array(
					$this->_entityid, 
					$new_revisionid, 
					$this->_key, 
					$this->_value, 
					date('c'), 
					$_SERVER['REMOTE_ADDR']
				)
			);

			if($st === FALSE) {
				return FALSE;
			}

			$this->_revisionid = $new_revisionid;
		} else {
			return FALSE;
		}

		return $st;
	}

	public function setEntityid($entityid) {
		assert('is_string($entityid)');

		$this->_entityid = $entityid;

		$this->_modified = TRUE;
	}
	
	public function setRevisionid($revisionid) {
		assert('ctype_digit($revisionid)');

		$this->_revisionid = $revisionid;

		$this->_modified = TRUE;
	}

	public function setKey($key) {
		assert('is_string($key)');

		$this->_key = $key;

		$this->_modified = TRUE;
	}

	public function setValue($value) {
		assert('is_string($value)');

		$this->_value = $value;

		$this->_modified = TRUE;
	}

	public function getEntityid() {
		return $this->_entityid;
	}

	public function getRevisionid() {
		return $this->_revisionid;
	}

	public function getKey() {
		return $this->_key;
	}

	public function getValue() {
		return $this->_value;
	}
}
?>
