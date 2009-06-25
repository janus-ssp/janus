<?php
/**
 * Contains Entity class for JANUS.
 *
 * @author Jacob Christianasen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Class implementing a JANUS entity.
 *
 * Entity class that extends the Database class implementing basic 
 * functionality user for entity generation and management.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @todo Remove default values on _system, _state, _type in final release.
 */
class sspmod_janus_Entity extends sspmod_janus_Database {

	/**
	 * Entity id
	 * @var string
	 */
	private $_entityid;
	
	/**
	 * Revision id.
	 * @var int Revision number.
	 */
	private $_revisionid;
	
	/**
	 * Curent system
	 * @var string
	 */
	private $_system = 'test';
	
	/**
	 * Current state
	 * @var string
	 */
	private $_state = 'accepted';

	/**
	 * Entity type.
	 * @var string
	 */
	private $_type = 'idp';

	/**
	 * Expiration date of current entity.
	 * @var DateTime
	 */
	private $_expiration;
	
	/**
	 * URL of the entities metadata
	 * @var string
	 */
	private $_metadataurl;
	
	/**
	 * Entity allowes all other entities
	 * @var string Is string to start with. NOTE! Will be changed in the future.
	 * @todo Figure out how to do this the right way.
	 */
	private $_allowedall = 'yes';

	/**
	 * List of allowed entities.
	 * @var string Is string to start with. NOTE! Will be changed in the future.
	 * @todo Figure out how to do this the right way.
	 */
	private $_allowedlist;

	/**
	 * Authentication context of entity.
	 * @var string
	 */
	private $_authcontext;

	/**
	 * Indicates whether that entity data has been modified.
	 * @var bool
	 */
	private $_modified = FALSE;

	/**
	 * Class constructor
	 *
	 * Class constructor that parses the configuration and initializes the entity
	 * object.
	 *
	 * @param array $config Configuration for the database
	 * @param string $entityid Entity id, default NULL
	 */
	public function __construct($config) {
		// To start with only the store config is parsed til user
		parent::__construct($config);
	}

	/**
	 * Save entity data.
	 *
	 * Method for saving the entity data to the database. If the entity data have
	 * not been modified since last load, the method returns TRUE without saving.
	 * Methos returns FALSE if an error has occured otherwise it will return TRUE
	 * on success.
	 *
	 * @return PDOStatement|bool Returns the statement on success.
	 * @todo Set modified.
	 */
	public function save() {
		if(!$this->_modified) {
			return TRUE;
		}

		if(!empty($this->_entityid)) {
			// Get next revisionid
			$st = $this->execute(
				'SELECT MAX(`revisionid`) AS maxrevisionid FROM '. self::$prefix .'__entity WHERE `entityid` = ?;',
				array($this->_entityid)
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
				INSERT INTO '. self::$prefix .'__entity (`entityid`, `revisionid`, `system`, `state`, `type`, `expiration`, `metadataurl`, `allowedall`, `allowedlist`, `authcontext`, `created`, `ip`) 
				VALUES 
				(?, ?, ? ,?, ?, ?, ?, ?, ?, ?, ?, ?);',
				array(
					$this->_entityid, 
					$new_revisionid, 
					$this->_system, 
					$this->_state, 
					$this->_type,
					$this->_expiration, 
					$this->_metdataurl, 
					$this->_allowedall, 
					$this->_allowedlist, 
					$this->_authcontext, 
					date('c'), 
					$_SERVER['REMOTE_ADDR'])
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

	/**
	 * Get newets revision id.
	 *
	 * Get the newest revision id from the entity. The value is set in the 
	 * instance.
	 *
	 * @return bool TRUE on success and FALSE on error
	 */
	private function newestRevision() {
		
		$st = $this->execute(
			'SELECT MAX(`revisionid`) AS maxrevisionid FROM '. self::$prefix .'__entity WHERE `entityid` = ?;',
			array($this->_entityid)
		);
		if($st === FALSE) {
			return FALSE;
		}
		$row = $st->fetchAll(PDO::FETCH_ASSOC);

		if($row[0]['maxrevisionid'] === NULL) {
			return FALSE;
		} else {
			$this->_revisionid= $row[0]['maxrevisionid'];
		}
		return TRUE;
	}

	/**
	 * Load entity data.
	 *
	 * Loads the entity data from the database. If either _entityid og _revisionid 
	 * is not set or an error occures, the method returns FALSE. Otherwise it will
	 * return the statement executed.
	 *
	 * @return PDOStatement|bool The statement or FALSE is an error occures.
	 * @todo Proper valildation
	 */
	public function load() {
		if(!empty($this->_entityid) && is_null($this->_revisionid)) {
			if(!$this->newestRevision()) {
				SimpleSAML_Logger::error('JANUS:Entity:load - Could not get newest revision.');
				return FALSE;
			}
		}
		if(empty($this->_entityid) || is_null($this->_revisionid)) {
			SimpleSAML_Logger::error('JANUS:Entity:load - entityid and revisionid needs to bes set.');
			return FALSE;
		}

		$st = $this->execute(
			'SELECT * FROM '. self::$prefix .'__entity WHERE `entityid` = ? AND `revisionid` = ?;', 
			array($this->_entityid, $this->_revisionid)
		);

		if($st === FALSE) {
			return FALSE;
		}

		while($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->_entityid = $row['entityid'];
			$this->_revisionid = $row['revisionid'];
			$this->_system = $row['system'];
			$this->_state = $row['state'];
			$this->_type = $row['type'];
			$this->_expiration = $row['expiration'];
			$this->_metadataurl = $row['metadataurl'];
			$this->_allowedall = $row['allowedall'];
			$this->_allowedlist = $row['allowedlist'];
			$this->_authcontext = $row['authcontext'];
			
			$this->_modify	 = FALSE;
		}
		
		return $st;
	}

	/**
	 * Set entity id
	 *
	 * Method for setting the entity id. A valid entityid is required to load a
	 * entity from the database. Method sets _modified to TRUE.
	 *
	 * @var string $entityid Entity id
	 */
	public function setEntityid($entityid) {
		assert('is_string($entityid)');

		$this->_entityid = $entityid;

		$this->_modified = TRUE;
	}

	/**
	 * Set revision id.
	 *
	 * Method for setting the revision id. The revision id is automaticlly 
	 * increased by one when the entity is saved. Method sets _modified to TRUE.
	 *
	 * @param int $revisionid Revision id of entity.
	 */
	public function setRevisionid($revisionid) {
		assert('ctype_digit($revisionid)');

		$this->_revisionid = $revisionid;

		$this->_modified = TRUE;
	}

	/**
	 * Set system for entity.
	 *
	 * Sets the system in which the entity resides. Method sets _modified to TRUE.
	 *
	 * @param string System name
	 */
	public function setSystem($system) {
		assert('is_string($system)');
	
		if($system != $this->_system) {	
			$this->_system = $system;
			$this->_modified = TRUE;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Set state
	 *
	 * Set the entity state
	 *
	 * @param string $state Entity state
	 */
	public function setState($state) {
		assert('is_string($state)');

		if($state != $this->_state) {
			$this->_state = $state;
			$this->_modified = TRUE;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Set type
	 *
	 * Set the entity type
	 *
	 * @param $type Entity type
	 */
	public function setType($type) {
		assert('is_string($type)');

		if($type != $this->_type) {
			$this->_type = $type;
			$this->_modified = TRUE;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Get revision id
	 *
	 * Get entity revision id
	 *
	 * @return int Revision id
	 */
	public function getRevisionid() {
		return $this->_revisionid;
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
	 * Get type
	 *
	 * Get the entity type.
	 *
	 * @return string Entoty type
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * Get system
	 *
	 * Get the entity system.
	 *
	 * @return string Entity system
	 */
	public function getSystem() {
		return $this->_system;
	}

	/**
	 * Get state
	 *
	 * Get the entity state.
	 *
	 * @return Entity state
	 */
	public function getState() {
		return $this->_state;
	}

	public function setAllowedall($allowedall) {
		assert('is_string($allowedall)');

		if($allowedall != $this->_allowedall) {
			$this->_allowedall = $allowedall;
			$this->_modified = TRUE;
			return TRUE;
		}
		return FALSE;
	}

	public function getAllowedall() {
		return $this->_allowedall;
	}
}
?>
