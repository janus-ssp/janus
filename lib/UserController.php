<?php
/**
 * Contains UserController for JANUS.
 *
 * @author Jacob Chriatiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Controller class for users.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_UserController extends sspmod_janus_Database{
	
	/**
	 * JANUS configuration
	 *
	 * @var SimpleSAML_Configuration
	 */
	private $_config;

	/**
	 * JANUS user
	 * 
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
	 * Constructs a UserController object.
	 *
	 * @param SimpleSAML_Configuration $config JANUS configuration
	 */
	public function __construct(SimpleSAML_Configuration &$config) {
		// Send DB config to parent class
		parent::__construct($config->getValue('store'));

		$this->_config = $config;
	}

	/**
	 * Set User
	 *
	 * Set the User for the object.
	 *
	 * @param string $user The user email.
	 * @return sspmod_janus_User|bool Returns the user or FALSE if the user can not be loaded.
	 * @throws InvalidArgumentException
	 */
	public function setUser($user) {
		// If $user is an email address
		if(is_string($user)) {
			$this->_user = new sspmod_janus_User($this->_config->getValue('store'));
			$this->_user->setEmail($user);
			if(!$this->_user->load(sspmod_janus_User::EMAIL_LOAD)) {
				return FALSE;
			}
		// If $user is a sspmod_janus_User object
		} else if(is_a($user, 'sspmod_janus_User')) {
			$this->_user = $user;
		} else {
			throw new InvalidArgumentException('Argument must be an email address or instance of sspmod_janus_User.');
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
			$entity = new sspmod_janus_Entity($this->_config->getValue('store'));
			$entity->setEntityid($row['entityid']);
			if(!$entity->load()) {
				$this->_entities[] = $entity;
			} else {
				SimpleSAML_Logger::error('JANUS:UserController:loadEntities - Entity could not be loaded, entity id: '.$row['entityid']);
			}
		}
		return TRUE;	
	}

	/**
	 * Get user entities
	 *
	 * Return all entities the user has access to.
	 *
	 * @param bool $force Force the method to reload the list of entities
	 * @return bool|array Array of sspmod_janus_Entity or FALSE on error.
	 */
	public function getEntities($force = FALSE) {
		assert('is_bool($force);');

		if(empty($this->_entities) || $force) {
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
	 * @return sspmod_janus_Entity|bool Returns the entity or FALSE on error.
	 */
	public function createNewEntity($entityid) {
		assert('is_string($entityid)');

		// Check if the entity id is already used
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

		$entity = new sspmod_janus_Entity($this->_config->getValue('store'));
		$entity->setEntityid($entityid);
		$entity->save();

		$st = $this->execute(
			'INSERT INTO '. self::$prefix .'__hasEntity (`uid`, `entityid`, `created`, `ip`) VALUES (?, ?, ?, ?);', 
			array($this->_user->getUid(), $entityid, date('c'), $_SERVER['REMOTE_ADDR'])
		);

		if($st === FALSE) {
			return FALSE;
		}

		// Reset list of entities
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
