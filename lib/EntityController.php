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
 * Controller for managing the connection between entities and its metadata. 
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
	 * JANUS entity
	 * @var sspmod_janus_Entity
	 */
	private $_entity;

	/**
	 * List of entity metadata
	 * @var array List of sspmod_janus_Metadata
	 */
	private $_metadata;

	/**
	 * List of attributes
	 * @var array List of sspmod_janus_Attribute
	 */
	private $_attributes;

	/**
	 * Class constructor.
	 *
	 * Constructs a EntityController object.
	 *
	 * @param SimpleSAML_Configuration $config Global SSP configuration
	 */
	public function __construct(SimpleSAML_Configuration &$config) {
		parent::__construct($config->getValue('store'));
		$this->_config = $config;
	}

	/**
	 * Set the entity.
	 *
	 * Set the entity either by entityid or by an sspmod_janus_Entity object. If
	 * an entityid is given the entity is loaded at the same time.
	 *
	 * @param string|sspmod_janus_Entity $entity Entity id or an entity object
	 * @param string $revisionid Revision id, for loading a previous revision.
	 * @return sspmod_janus_Entity|false Returns the entity or false on error.
	 */
	public function setEntity($entity, $revisionid = NULL) {
		// If entity is given by entityid
		if(is_string($entity)) {
			// Create a new entity
			$this->_entity = new sspmod_janus_Entity($this->_config->getValue('store'));
			$this->_entity->setEntityid($entity);
			// If a revisionid is parsed
			if(isset($revisionid)) {
				assert('ctype_digit($revisionid);');
				$this->_entity->setRevisionid($revisionid);
			}
			// Load entity information
			if(!$this->_entity->load()) {
				SimpleSAML_Logger::error('JANUS:EntityController:setEntity - Entity could not load. Eid: '. $entity . ' - Rid: '. $revisionid);
				return FALSE;
			}
			// If entity is given by entity object
		} else if($entity instanceof sspmod_janus_Entity) {
			$this->_entity = $entity;
		}

		return $this->_entity;
	}

	/**
	 * Load metadata.
	 *
	 * Loades the metadata associated with the entity.
	 *
	 * @return bool Return true on success and false on error.
	 */
	private function loadMetadata() {
		assert('$this->_entity instanceof sspmod_janus_Entity');

		$st = $this->execute(
							 'SELECT * FROM '. self::$prefix .'__metadata WHERE `entityid` = ? AND `revisionid` = ?;',
							 array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
							);

		if($st === FALSE) {
			SimpleSAML_Logger::error('JANUS:EntityController:loadMetadata - Metadata could not load.');
			return FALSE;	
		}
		$this->_metadata = array();
		$rs = $st->fetchAll(PDO::FETCH_ASSOC);
		foreach($rs AS $row) {
			$metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
			$metadata->setEntityid($row['entityid']);
			$metadata->setRevisionid($row['revisionid']);
			$metadata->setKey($row['key']);
			if(!$metadata->load()) {
				die('nom load');
			}
			$this->_metadata[] = $metadata;
		}
		return TRUE;	
	}

	/**
	 * Get entity metadata.
	 *
	 * Returns the metadata for the entity.
	 *
	 * @return array An array of sspmod_janus_Metadata.
	 */
	public function getMetadata() {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		if(empty($this->_metadata)) {
			if(!$this->loadMetadata()) {
				return FALSE;
			}
		}
		return $this->_metadata;
	}

	/**
	 * Load attributes.
	 *
	 * Loades the attributes associated with the entity.
	 *
	 * @return bool Return true on success and false on error.
	 */
	private function loadAttributes() {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		$st = $this->execute(
							 'SELECT * FROM '. self::$prefix .'__attribute WHERE `entityid` = ? AND `revisionid` = ?;',
							 array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
							);

		if($st === FALSE) {
			SimpleSAML_Logger::error('JANUS:EntityController:loadAttributes - Attributes could not load.');
			return FALSE;	
		}
		$this->_attributes = array();
		$rs = $st->fetchAll(PDO::FETCH_ASSOC);
		foreach($rs AS $row) {
			$attribute = new sspmod_janus_Attribute($this->_config->getValue('store'));
			$attribute->setEntityid($row['entityid']);
			$attribute->setRevisionid($row['revisionid']);
			$attribute->setKey($row['key']);
			if(!$attribute->load()) {
				die('Attribute: no load');
			}
			$this->_attributes[] = $attribute;
		}
		return TRUE;	
	}

	/**
	 * Get entity attributes.
	 *
	 * Returns the attributes for the entity.
	 *
	 * @return array An array of sspmod_janus_Attribute.
	 */
	public function getAttributes() {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		if(empty($this->_attributes)) {
			if(!$this->loadAttributes()) {
				return FALSE;
			}
		}
		return $this->_attributes;
	}

	/**
	 * Add an attribute.
	 *
	 * Add a new attribute to the entity.
	 *
	 * @param string $key The attribute key.
	 * @param string $value The attribute value.
	 * @return sspmod_janus_Attribute The attribute.
	 */
	public function addAttribute($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		if(empty($this->_attributes)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}


		$st = $this->execute(
							 'SELECT count(*) AS count FROM '. self::$prefix .'__attribute WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;',
							 array($this->_entity->getEntityid(), $this->_entity->getRevisionid(), $key)
							);
		if($st === FALSE) {
			SimpleSAML_Logger::error('JANUS:EntityController:createNewAttribute - Count check failed');
			return FALSE;
		}

		$row = $st->fetchAll(PDO::FETCH_ASSOC);

		if($row[0]['count'] > 0) {
			SimpleSAML_Logger::error('JANUS:EntityController:createNewAttribute - Attribute already exists');
			return FALSE;
		}

		$attribute = new sspmod_janus_Attribute($this->_config->getValue('store'));
		$attribute->setEntityid($this->_entity->getEntityid());
		// revision id is not set, since it is not save to the db and hence it do not have a reversionid
		//$attribute->setRevisionid($this->_entity->getrevisionid());
		$attribute->setKey($key);
		$attribute->setValue($value);
		$this->_attributes[] = $attribute;
		// The metadata is not saved, since it is not part of the current entity with current revision id
		//$attribute->save();

		return $attribute;
	}

	/**
	 * Add metadata.
	 *
	 * Add a new matadata entry to the entity.
	 *
	 * @param string $key The metadata key.
	 * @param string $value The metadata value.
	 * @return sspmod_janus_Metadata The metadata.
	 */
	public function addMetadata($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		if(empty($this->_metadata)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}


		$st = $this->execute(
							 'SELECT count(*) AS count FROM '. self::$prefix .'__metadata WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;',
							 array($this->_entity->getEntityid(), $this->_entity->getRevisionid(), $key)
							);
		if($st === FALSE) {
			SimpleSAML_Logger::error('JANUS:EntityController:createNewMetadata - Count check failed');
			return FALSE;
		}

		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		if($row[0]['count'] > 0) {
			SimpleSAML_Logger::error('JANUS:EntityController:createNewMetadata - Metadata already exists');
			return FALSE;
		}

		$metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
		$metadata->setEntityid($this->_entity->getEntityid());
		// revision id is not set, since it is not save to the db and hence it do not have a reversionid
		//$metadata->setRevisionid($this->_entity->getrevisionid());
		$metadata->setKey($key);
		$metadata->setValue($value);
		$this->_metadata[] = $metadata;
		// The metadata is not saved, since it is not part of the current entity with current revision id
		//$metadata->save();

		return $metadata;
	}

	/**
	 * Save the entity.
	 *
	 * Writes the entity and the corresponding metadata and attributes to the 
	 * database, increasing the revisionid by one.
	 * 
	 * @return bool True on success and false on error.
	 * @todo Handle errors from save.
	 */
	public function saveEntity()  {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		$this->_entity->save();
		$new_revisionid = $this->_entity->getRevisionid();


		foreach($this->_metadata AS $data) {
			$data->setRevisionid($new_revisionid);
			$data->save();
		}  

		foreach($this->_attributes AS $data) {
			$data->setRevisionid($new_revisionid);
			$data->save();
		}

		return TRUE;	
	}

	/**
	 * Load entity.
	 *
	 * Loads the metadata and attributes associated with the entity.
	 *
	 * @return bool Return true on success and false on error.
	 * @todo Handle error from function calls.  
	 */
	public function loadEntity() {	
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		$this->getMetadata();
		$this->getAttributes();

		return TRUE;
	}

	/**
	 * Get the entity history.
	 *
	 * Returns an array of entities. One for each revision.
	 *
	 * @return array|bool An array of sspmod_janus_Entity or FALSE on error
	 */
	public function getHistory() {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		//assert('is_a($this->_entity, "sspmod_janus_Entity")');

		$st = $this->execute(
							 'SELECT * FROM '. self::$prefix .'__entity WHERE `entityid` = ?', 
							 array($this->_entity->getEntityid())
							);

		if($st === FALSE) {
			return FALSE;
		}

		$rs = $st->fetchAll(PDO::FETCH_ASSOC);

		$history = array();
		foreach($rs AS $data) {
			$entity = new sspmod_janus_Entity($this->_config->getValue('store'));
			$entity->setEntityid($this->_entity->getEntityid());
			$entity->setRevisionid($data['revisionid']);
			if(!$entity->load()) {
				SimpleSAML_Logger::error('JANUS:EntityController:getHistory - Entity could not load. Eid: '. $this->_entity->getEntityid() . ' - Rid: '. $data['revisionid']);
				return FALSE;
			}
			$history[] = $entity;
		}

		return $history;
	}
}
?>
