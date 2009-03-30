<?php
/**
 * Contains MetadataController for JANUS. 
 *
 * @author Jacob Chriatiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Controller classe for metadata.
 *
 * Class user to control the connection between entities and its metadata. The 
 * class offers methods for getting the entities metadata and creation of new 
 * metadata and connect it to the entity.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_MetadataController extends sspmod_janus_Database{

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
	 * Revision id
	 * @var int
	 */
	private $_revisionid;
	
	/**
	 * List of entity metadata
	 * @var array List of sspmod_janus_Metadata
	 */
	private $_metadata;

	/**
	 * Class constructor.
	 *
	 * Constructs af MetadataController object.
	 *
	 * @param SimpleSAML_Configuration $config Global SSP configuration
	 */
	public function __construct($config) {
		parent::__construct($config->getValue('store'));
		$this->_config = $config;
	}

	public function setEntity($entityid, $revisionid = null) {
		assert('is_string($entityid)');

		$this->_entity = new sspmod_janus_Entity($this->_config->getValue('store'));
		$this->_entity->setEntityid($entityid);

		if(isset($revision)) {
			assert('ctype_digit($revisionid),');
			$this->_entity->setRevisionid($revisionid);
		}

		if(!$this->_entity->load()) {
			return FALSE;
		}
		return $this->_entity;
	}

	public function setrevisionid($revisionid) {
		assert('ctype_digit($revisionid)');

		$this->_revisionid = $revisionid;
	}
	
	private function loadMetadata() {
		$st = $this->execute(
			'SELECT * FROM '. self::$prefix .'__metadata WHERE `entityid` = ? AND `revisionid` = ?;',
			array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
		);

		if($st === FALSE) {
			return FALSE;	
		}
		$this->_metadata = array();
		while($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->_metadata[] = $row;
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

	public function createNewMetadata($key, $value) {
		assert('is_string($key);');	
		assert('is_string($value);');

		$st = $this->execute(
			'SELECT count(*) AS count FROM '. self::$prefix .'__metadata WHERE `entityid` = ?;',
			array($entityid)
		);

		if($st === FALSE) {
			return FALSE;
		}

		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		if($row[0]['count'] > 0) {
			return FALSE;
		}

		$metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
		$metadata->setKey($key);
		$metadata->setValue($value);
		$metadata->setEntityid($this->_entity->getEntityid());
		$metadata->setRevisionid($this->_entity->getRevisionid());
		$metadata->save();

		return $metadata;

	}
}
?>
