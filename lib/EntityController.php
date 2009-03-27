<?php
/**
 * Contains EntityController for JANUS.
 *
 * @author Jacob Chriatiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Controller classe for entities.
 *
 * Class user to control the connection between users and their entities. The 
 * class offers methods for getting the users entities and creation of new 
 * entities and connect it to the user.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_EntityController extends sspmod_janus_Database{

	/**
	 * JANUS configuration
	 * @var SimpleSAML_Configuration
	 */
	private $_config;

	/**
	 * JANUS user
	 * @var sspmod_janus_User
	 */
	private $_user;
	
	/**
	 * List of user entities
	 * @var array List of sspmod_janus_Entity
	 */
	private $_entities;

	/**
	 * Class constructor.
	 *
	 * Constructs af EntityController object.
	 *
	 * @param SimpleSAML_Configuration $config Global SSP configuration
	 */
	public function __construct($config) {
		parent::__construct($config->getValue('store'));
		$this->_config = $config;
	}

	/**
	 * Set User
	 *
	 * Set the User for the object. The method creates a new sspmod_janus_User
	 * object.
	 *
	 * @param string $user The user email.
	 * @return sspmod_janus_User|bool Returns the user or FALSE on error.
	 * @todo Overvej om man ikke bare skal sende et User object i stedet? 
	 */
	public function setUser($user) {
		assert('is_string($user)');

		$this->_user = new sspmod_janus_User($this->_config->getValue('store'));
		$this->_user->setEmail($user);
		if(!$this->_user->load(sspmod_janus_User::EMAIL_LOAD)) {
			return FALSE;
		}

		return $this->_user;
	}
	
	/**
	 * Load users entities
	 *
	 * Load all the entities that the user has access to.
	 *
	 * @return bool TRUE on success and FALSE on error.
	 */
	private function loadEntities() {
		
		$st = $this->execute(
			'SELECT * FROM '. self::$prefix .'__hasEntity WHERE `uid` = ?;',
			array($this->_user->getUid())
		);

		if($st === FALSE) {
			return FALSE;	
		}

		$this->_entities = array();
		while($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->_entities[] = $row;
		}
		return TRUE;	
	}

	/**
	 * Get user entities
	 *
	 * Return all entities the user has access to.
	 *
	 * @return array Array of entity is's.
	 */
	public function getEntities() {

		if(empty($this->_entities)) {
			if(!$this->loadEntities()) {
				return FALSE;
			}
		}

		return $this->_entities;
	}

	/**
	 * Create new entity
	 *
	 * Create a new Entity and connects it to the user.
	 *
	 * @param string $entityid Entity id for the new Entity
	 * @return sspmod_janus_Entity|bool Returns the Entity og FALSE on error.
	 */
	public function createNewEntity($entityid) {
		assert('is_string($entityid)');

		$st = $this->execute(
			'SELECT count(*) AS count FROM '. self::$prefix .'__entity WHERE `entityid` = ?;',
			array($entityid)
		);

		if($st === FALSE) {
			return FALSE;
		}

		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		if($row[0]['count'] > 0) {
			return FALSE;
		}

		$entity = new sspmod_janus_Entity($this->_config->getValue('store'), $entityid);
		$entity->save();

		$st = $this->execute(
			'INSERT INTO '. self::$prefix .'__hasEntity (`uid`, `entityid`, `created`, `ip`) VALUES (?, ?, ?, ?);', 
			array($this->_user->getUid(), $entityid, date('c'), $_SERVER['REMOTE_ADDR'])
		);

		if($st === FALSE) {
			return FALSE;
		}

		$this->_entities = NULL;

		return $entity;
	}

	/*
	 * DELETE - ONLY FOR TEST PURPOSE
	 */
	public function getUsers() {
		$st = $this->execute('SELECT * FROM '. self::$prefix .'__user;', array());
		
		return $st->fetchAll(PDO::FETCH_ASSOC);
	}
}
?>
