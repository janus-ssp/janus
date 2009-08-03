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

	private $_blocked;

	private $_users;

	private $_modified;
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
		$this->_modified = FALSE;
	}

	/**
	 * Set the entity.
	 *
	 * Set the entity either by entityid or by an sspmod_janus_Entity object. If
	 * an entityid is given the entity is loaded at the same time. 
	 * Use "= &" on the returned entity if you want to make updates to the 
	 * entity.
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
			$this->_entity->setEid($entity);
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
				die('no load');
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

		if(empty($this->_metadata)) {
			if(!$this->loadMetadata()) {
				return FALSE;
			}
		}
		return $this->_metadata;
	}

	public function getBlockedEntities() {
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_blocked)) {
			if(!$this->loadBlockedEntities()) {
				return FALSE;
			}
		}
		return $this->_blocked;
	
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
		// Revision id is not set, since it is not saved to the db and hence it do not have a reversionid
		$attribute->setKey($key);
		$attribute->setValue($value);
		$this->_attributes[] = $attribute;
		// The metadata is not saved, since it is not part of the current entity with current revision id

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

		$pattern = '/*((\w+\:)*\w{2})|(\w+\:?\w+)$/';
		//$pattern = '/^\w(\:\w+)+(\:\w{2})?$/';
		//$pattern = '/^(.+)(\:\w{2})*?$/';
		//$pattern = '/^(\w+(\:{1}\w+)*)(\:{1}\w{2})?$/';
		//preg_match($pattern, $key, $matches);
		//var_dump($matches);
		//die();

		// Check if metadata is allowed
		if($this->_entity->getType() == 'idp' && !(in_array('USERDEFINED', $this->_config->getValue('metadatafields.'. $this->_entity->getType())) || in_array($key, $this->_config->getValue('metadatafields.'.$this->_entity->getType())))) {
			SimpleSAML_Logger::info('JANUS:EntityController:createNewMetadata - Metadata key \''. $key .' not allowed');
			return FALSE;
		}
		
		// Check if metadata is allowed
		if($this->_entity->getType() == 'sp' && !(in_array('USERDEFINED', $this->_config->getValue('metadatafields.'.$this->_entity->getType())) || in_array($key, $this->_config->getValue('metadatafields.'.$this->_entity->getType())))) {
			SimpleSAML_Logger::info('JANUS:EntityController:createNewMetadata - Metadata key \''. $key .' not allowed');
			return FALSE;
		}

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
		// Revision id is not set, since it is not save to the db and hence it do not have a reversionid
		$metadata->setKey($key);
		$metadata->setValue($value);
		$this->_metadata[] = $metadata;
		// The metadata is not saved, since it is not part of the current entity with current revision id

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
	 * @todo Handle entity with out metadata and/or attributes.
	 */
	public function saveEntity()  {
		assert('$this->_entity instanceof sspmod_janus_Entity');

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

		$this->saveBlockedEntities($new_revisionid);

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

		$this->getMetadata();
		$this->getAttributes();
		$this->getBlockedEntities();
		$this->getUsers();

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

		$st = $this->execute(
							 'SELECT * FROM '. self::$prefix .'__entity WHERE `eid` = ? ORDER BY `revisionid` DESC', 
							 array($this->_entity->getEid())
							);

		if($st === FALSE) {
			return FALSE;
		}

		$rs = $st->fetchAll(PDO::FETCH_ASSOC);

		$history = array();
		foreach($rs AS $data) {
			$entity = new sspmod_janus_Entity($this->_config->getValue('store'));
			$entity->setEid($this->_entity->getEid());
			$entity->setRevisionid($data['revisionid']);
			if(!$entity->load()) {
				SimpleSAML_Logger::error('JANUS:EntityController:getHistory - Entity could not load. Eid: '. $this->_entity->getEntityid() . ' - Rid: '. $data['revisionid']);
				return FALSE;
			}
			$history[] = $entity;
		}

		return $history;
	}

	/*
	 * Get entity
	 *
	 * Get the entity.
	 *
	 * @return sspmod_janus_Entity
	 */
	public function getEntity() {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		
		return $this->_entity;
	}

	/*
	 * Import SP SAML 2.0 metadata.
	 *
	 * Imports SP SAML 2.0 metadata. The entity id is conpared with that entity id 
	 * given in the metadata parsed.
	 *
	 * @param string $metadata SAML 2.0 metadata
	 * @return bool Return TRUE on success and FALSE on error or if no metadata was added.
	 */
	public function importMetadata20SP($metadata) {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		assert('$this->_entity->getType() == \'sp\'');
		assert('is_string($metadata)');

		$update = FALSE;

		// Parse metadata
		try {
			$parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);  
		} catch (Exception $e) {
			SimpleSAML_Logger::error('JANUS:EntityController:importMetadata20SP - Metadata not valid SAML 2.0');
			return FALSE;
		}

		$parsedmetadata = $parser->getMetadata20SP();

		// If metadata was not parsed
		if($parsedmetadata === NULL) {
			return FALSE;
		}

		// Remove entity descriptor
		unset($parsedmetadata['entityDescriptor']);

		// Validate that entity id is the same forimportted metadata and entity
		if($parsedmetadata['entityid'] != $this->_entity->getEntityid()) {
			return 'error_entityid_no_match';	
		} else {
			unset($parsedmetadata['entityid']);
		}
			
		// Add metadata fields
		foreach($parsedmetadata AS $key => $value) {
			if($this->addMetadata($key, $value)) {
				$update = TRUE;
			}
		}

		return 'status_metadata_parsed_ok';
	}
	
	/*
	 * Import IdP SAML 2.0 metadata.
	 *
	 * Imports IdP SAML 2.0 metadata. The entity id is conpared with that entity id 
	 * given in the metadata parsed.
	 *
	 * @param string $metadata SAML 2.0 metadata
	 * @return bool Return TRUE on success and FALSE on error or if no metadata was added.
	 */
	public function importMetadata20IdP($metadata) {
		assert('$this->_entity instanceof sspmod_janus_Entity');
		assert('$this->_entity->getType() == \'idp\'');
		assert('is_string($metadata)');

		$update = FALSE;

		// Parse metadata
		try {
			$parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);  
		} catch (Exception $e) {
			SimpleSAML_Logger::error('JANUS:EntityController:importMetadata20IdP - Metadata not valid SAML 2.0');
			return 'error_metadata_not_parsed';
		}

		$parsedmetadata = $parser->getMetadata20IdP();

		// If metadata was not parsed
		if($parsedmetadata === NULL) {
			return 'error_metadata_not_parsed';
		}

		// Remove entity descriptor
		unset($parsedmetadata['entityDescriptor']);

		// Validate that entity id is the same forimportted metadata and entity
		if($parsedmetadata['entityid'] != $this->_entity->getEntityid()) {
			return 'error_entityid_no_match';	
		} else {
			unset($parsedmetadata['entityid']);
		}
			
		// Add metadata fields
		foreach($parsedmetadata AS $key => $value) {
			if($key == 'name') {
				if(is_array($value)) {
					foreach($value AS $langkey => $metadatavalue) {
						if($this->addMetadata('organization:name:' . $langkey, $metadatavalue)) {
							$update = TRUE;
						}
					}
				} else {
					// Should not happen
					continue;
				}
			} elseif($key == 'description') {
				// Not user in SAML2 metadata. Only used in SSP flatfile metadata
				continue;
			} elseif($key == 'certFingerprint') {
				if(is_array($value)) {
					foreach($value AS $metadatakey => $metadatavalue) {
						if($this->addMetadata('certFingerprint', $metadatavalue)) {
							$update = TRUE;
						}
					}
				} else {
					// Should not happen
					continue;
				}
			} else {
				if($this->addMetadata($key, $value)) {
					$update = TRUE;
				}
			}	
		}

		return 'status_metadata_parsed_ok';
	}
	
	/*
	 * Udate attribute
	 *
	 * Update the given attribute.
	 *
	 * @param string $key Attribute key
	 * @param atring $value Attribute value
	 * @return bool Return TRUE on success and FLASE on error
	 */ 
	public function updateAttribute($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_attributes)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}

		$update = FALSE;

		foreach($this->_attributes AS &$data) {
			if($data->getKey() == $key) {
				$data->setValue($value);
				$update = TRUE;
			}
		}

		return $update;
	}
	
	/*
	 * Udate metadata
	 *
	 * Update the given metadata.
	 *
	 * @param string $key Metadata key
	 * @param atring $value Metadata value
	 * @return bool Return TRUE on success and FLASE on error
	 */ 
	public function updateMetadata($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_metadata)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}

		$update = FALSE;

		foreach($this->_metadata AS &$data) {
			if($data->getKey() === $key && $data->getValue() !== $value) {
				$data->setValue($value);
				$update = TRUE;
			}
		}

		return $update;
	}

	/*
	 * Remove metadata
	 *
	 * Remove the give metadata fom the EntityController. The metadata will only 
	 * be removed from the entity if you call saveEntity().
	 *
	 * @param string Metadata key
	 * @return bool Returns TRUE on success and FALSE on error
	 */
	public function removeMetadata($key) {
		assert('is_string($key);');
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_metadata)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}

		$update = FALSE;
		
		foreach($this->_metadata AS $index => &$data) {
			if($data->getKey() == $key) {
				unset($this->_metadata[$index]);
				$update = TRUE;
			}
		}

		return $update;
	}
	
	/*
	 * Remove attribute
	 *
	 * Remove the give attribute from the EntityController. The attribute will only 
	 * be removed from the entity if you call saveEntity().
	 *
	 * @param string Attribute key
	 * @return bool Returns TRUE on success and FALSE on error
	 */
	public function removeAttribute($key) {
		assert('is_string($key);');
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_attributes)) {
			if(!$this->loadEntity()) {
				return FALSE;
			}
		}

		$update = FALSE;

		foreach($this->_attributes AS $index => &$data) {
			if($data->getKey() == $key) {
				unset($this->_attributes[$index]);
				$update = TRUE;
			}
		}

		return $update;
	}

	public function addBlockedEntity($remoteentityid) {
		assert('is_string($remoteentityid)');

		if(!array_key_exists($remoteentityid, $this->_blocked)) {
			$this->_blocked[$remoteentityid] = array('remoteentityid' => $remoteentityid);

			$this->_modified = TRUE;
		}
		//$this->setAllowedAll('TRUE');

		return TRUE;
	}

	public function removeBlockedEntity($remoteentityid) {
		assert('is_string($remoteentityid)');

		unset($this->_blocked[$remoteentityid]);

		$this->_modified =TRUE;

		return TRUE;
	}

	private function loadBlockedEntities() {
		$st = $this->execute(
							 'SELECT * FROM '. self::$prefix .'__blockedEntity WHERE `entityid` = ? AND `revisionid` = ?;',
							 array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
		);

		if($st === FALSE) {
			return FALSE;
		}
		
		$row = $st->fetchAll(PDO::FETCH_ASSOC);

		$this->_blocked = array();

		foreach($row AS $data) {
			$this->_blocked[$data['remoteentityid']] = $data;
		}
		$this->_modified = FALSE;

		return TRUE;
	
	}
	
	public function setAllowedAll($allowedall) {
		$return = $this->_entity->setAllowedall($allowedall);
		if($allowedall === 'yes') {
			$this->_blocked = array();
			$this->_modified = TRUE;
			return TRUE;
		}
		return $return;
	}

	private function saveBlockedEntities($revision) {
		
		if($this->_modified) {

			foreach($this->_blocked AS $blocked) {
				$st = $this->execute(
									 'INSERT INTO '. self::$prefix .'__blockedEntity (`entityid`, `revisionid`, `remoteentityid`, `created`, `ip`) VALUES (?, ?, ?, ?, ?);', 
									 array($this->_entity->getEntityid(), $revision, $blocked['remoteentityid'], date('c'), $_SERVER['REMOTE_ADDR'])
									);

				if($st === FALSE) {
					return FALSE;
				}
			}
		}
		return TRUE;

	}

	public function getUsers() {
		assert('$this->_entity instanceof sspmod_janus_Entity');

		if(empty($this->_users)) {
			if(!$this->loadUsers()) {
				return FALSE;
			}
		}
		return $this->_users;
	
	}
	
	private function loadUsers() {
		$st = $this->execute(
			'SELECT `email` FROM '. self::$prefix .'__hasEntity t1, '. self::$prefix .'__user t2  WHERE t1.`eid` = ? AND t1.`uid` = t2.`uid`;',
			array($this->_entity->getEid())
		);

		if($st === FALSE) {
			return FALSE;
		}
		
		$row = $st->fetchAll(PDO::FETCH_ASSOC);

		$this->_users = array();

		foreach($row AS $data) {
			$this->_users[$data['email']] = TRUE;
		}
		$this->_modified = FALSE;

		return TRUE;
	}
}
?>
