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

	public function setEntity($entity, $revisionid = null) {
		// If entity is given by entityid
		if(is_string($entity)) {
			$this->_entity = new sspmod_janus_Entity($this->_config->getValue('store'));
			$this->_entity->setEntityid($entity);
			if(isset($revisionid)) {
				assert('ctype_digit($revisionid),');
				$this->_entity->setRevisionid($revisionid);
			}
			if(!$this->_entity->load()) {
				SimpleSAML_Logger::error('JANUS:EntityController:setEntity - Entity could not load. Eid: '. $entity . ' - Rid: '. $revisionid);
				return FALSE;
			}
		// If entity is given by entity object
		} else if(is_a($entity, 'sspmod_janus_Entity')) {
			$this->_entity = $entity;
		}
		
		return $this->_entity;
	}

	private function loadMetadata() {
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		
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

	public function getMetadata() {
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		if(empty($this->_metadata)) {
			if(!$this->loadMetadata()) {
				return FALSE;
			}
		}
		return $this->_metadata;
	}

	private function loadAttributes() {
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
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

	public function getAttributes() {
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		if(empty($this->_attributes)) {
			if(!$this->loadAttributes()) {
				return FALSE;
			}
		}
		return $this->_attributes;
	}

	public function createNewAttribute($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		
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
		$attribute->setRevisionid($this->_entity->getrevisionid());
		$attribute->setKey($key);
		$attribute->setValue($value);
		$this->_attributes[] = $attribute;
		// The metadata is not saved, since it is not part of the current entity with current revision id
		//$attribute->save();
	
		return $attribute;
	}

	public function createNewMetadata($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		
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
		$metadata->setRevisionid($this->_entity->getrevisionid());
		$metadata->setKey($key);
		$metadata->setValue($value);
		$this->_metadata[] = $metadata;
		// The metadata is not saved, since it is not part of the current entity with current revision id
		//$metadata->save();
	
		return $metadata;
	}

	public function saveEntity()  {
		assert('is_a($this->_entity, "sspmod_janus_Entity")');

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

	// Implement load function
	public function loadEntity() {	
		assert('is_a($this->_entity, "sspmod_janus_Entity")');
		
		$this->getMetadata();
		$this->getAttributes();

		return TRUE;
	}
}
?>
