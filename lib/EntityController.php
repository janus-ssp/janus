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
		if(empty($this->_metadata)) {
			if(!$this->loadMetadata()) {
				return FALSE;
			}
		}
		return $this->_metadata;
	}

	private function loadAttributes() {}

	public function getAttributes() {}

	public function createNewMetadata($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		
		if(empty($this->_metadata)) {
			if(!$this->loadMetadata()) {
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

		$this->_entity->save();
		$new_revisionid = $this->_entity->getRevisionid();
		

		foreach($this->_metadata AS $data) {
			$data->setRevisionid($new_revisionid);
			$data->save();
		}  
		//Implement a save fuction
		// this function should increment the revision id to the newest id
	}

	// Implement load function
	public function loadEntity() {	
		$this->loadMetadata();
		$this->loadAttributes();
	}
}
?>
