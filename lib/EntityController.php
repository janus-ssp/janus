<?php
/**
 * Controller for entities
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
/**
 * Controller for entities
 *
 * Controller for managing the connection between entities and its metadata. 
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_EntityController extends sspmod_janus_Database
{
    /**
     * JANUS configuration
     * @var SimpleSAML_Configuration
     */
    private $_config;

    /**
     * JANUS entity
     * @var Sspmod_Janus_Entity
     */
    private $_entity;

    /**
     * List of entity metadata
     * @var array List of Sspmod_Janus_Metadata
     */
    private $_metadata;

    /**
     * List of attributes
     * @var array List of Sspmod_Janus_Attribute
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
     * @param SimpleSAML_Configuration &$config Global SSP configuration
     */
    public function __construct(SimpleSAML_Configuration $config)
    {
        parent::__construct($config->getValue('store'));
        $this->_config = $config;
        $this->_modified = false;
    }

    /**
     * Set the entity.
     *
     * Set the entity either by entityid or by an sspmod_janus_Entity object. If
     * an entityid is given the entity is loaded at the same time. 
     * Use "= &" on the returned entity if you want to make updates to the 
     * entity.
     *
     * @param string|sspmod_janus_Entity $entity     Entity id or an entity 
     * object
     * @param string                     $revisionid Revision id, for loading a
     * previous revision
     *
     * @return sspmod_janus_Entity|false Returns the entity or false on error
     */
    public function &setEntity($entity, $revisionid = null)
    {
        // If entity is given by entityid
        if (ctype_digit($entity)) {
            // Create a new entity
            $this->_entity
                = new sspmod_janus_Entity($this->_config->getValue('store'));
            $this->_entity->setEid($entity);
            // If a revisionid is parsed
            if (isset($revisionid)) {
                assert('ctype_digit($revisionid);');
                $this->_entity->setRevisionid($revisionid);
            }
            // Load entity information
            if (!$this->_entity->load()) {
                SimpleSAML_Logger::error(
                    'JANUS:EntityController:setEntity - Entity could not load.'
                    . ' Eid: '. $entity . ' - Rid: '. $revisionid
                );
                return false;
            }
            // If entity is given by entity object
        } else if ($entity instanceof Sspmod_Janus_Entity) {
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
    private function _loadMetadata()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'metadata 
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($this->_entity->getEid(), $this->_entity->getRevisionid())
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:_loadMetadata - Metadata could not load.'
            );
            return false;	
        }
        $this->_metadata = array();
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rs AS $row) {
            $metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
            $metadata->setEid($row['eid']);
            $metadata->setRevisionid($row['revisionid']);
            $metadata->setKey($row['key']);
            if (!$metadata->load()) {
                die('no load');
            }
            $this->_metadata[] = $metadata;
        }
        return true;
    }

    /**
     * Get entity metadata.
     *
     * Returns the metadata for the entity.
     *
     * @return array An array of sspmod_janus_Metadata.
     */
    public function getMetadata()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_metadata)) {
            if (!$this->_loadMetadata()) {
                return false;
            }
        }
        return $this->_metadata;
    }

    /**
     * Return blocked entities for the current entity
     *
     * @return array|false Return an array of blocked entities or false on error 
     *
     * @since Method available since Release 1.0.0
     */ 
    public function getBlockedEntities()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_blocked)) {
            if (!$this->_loadBlockedEntities()) {
                return false;
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
    private function _loadAttributes()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'attribute 
            WHERE `entityid` = ? AND `revisionid` = ?;',
            array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:_loadAttributes - Attributes could not load.'
            );
            return false;	
        }

        $this->_attributes = array();

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rs AS $row) {
            $attribute
                = new sspmod_janus_Attribute($this->_config->getValue('store'));
            $attribute->setEntityid($row['entityid']);
            $attribute->setRevisionid($row['revisionid']);
            $attribute->setKey($row['key']);
            if (!$attribute->load()) {
                die('Attribute: no load');
            }
            $this->_attributes[] = $attribute;
        }
        return true;	
    }

    /**
     * Get entity attributes.
     *
     * Returns the attributes for the entity.
     *
     * @return array An array of sspmod_janus_Attribute.
     */
    public function getAttributes()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_attributes)) {
            if (!$this->_loadAttributes()) {
                return false;
            }
        }
        return $this->_attributes;
    }

    /**
     * Add an attribute.
     *
     * Add a new attribute to the entity.
     *
     * @param string $key   The attribute key
     * @param string $value The attribute value
     *
     * @return sspmod_janus_Attribute The attribute.
     */
    public function addAttribute($key, $value)
    {
        assert('is_string($key);');	
        assert('is_string($value);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_attributes)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $st = $this->execute(
            'SELECT count(*) AS count 
            FROM '. self::$prefix .'attribute 
            WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;',
            array(
                $this->_entity->getEntityid(), 
                $this->_entity->getRevisionid(), 
                $key
            )
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:createNewAttribute - Count check failed'
            );
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);

        if ($row[0]['count'] > 0) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:addAttribute - Attribute already exists'
            );
            return false;
        }

        $attribute = new sspmod_janus_Attribute($this->_config->getValue('store'));
        $attribute->setEntityid($this->_entity->getEntityid());
        // Revision id is not set, since it is not saved to the db and hence it
        // do not have a reversionid
        $attribute->setKey($key);
        $attribute->setValue($value);
        $this->_attributes[] = $attribute;
        // The metadata is not saved, since it is not part of the current entity
        // with current revision id
        return $attribute;
    }

    /**
     * Add metadata.
     *
     * Add a new matadata entry to the entity.
     *
     * @param string $key   The metadata key
     * @param string $value The metadata value
     *
     * @return sspmod_janus_Metadata The metadata.
     * @todo Make independent of type (make generic, support for more types than 
     * sp and idp)
     */
    public function addMetadata($key, $value)
    {
        assert('is_string($key);');	
        assert('is_string($value);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $allowedfields 
            = $this->_config->getValue(
                'metadatafields.'. $this->_entity->getType()
            );

        // Check if metadata is allowed
        if (!( array_key_exists('USERDEFINED', $allowedfields) 
            || array_key_exists($key, $allowedfields))
        ) {
            SimpleSAML_Logger::info(
                'JANUS:EntityController:addMetadata - Metadata key \''
                . $key .' not allowed'
            );
            return false;
        }

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $st = $this->execute(
            'SELECT count(*) AS count 
            FROM '. self::$prefix .'metadata 
            WHERE `eid` = ? AND `revisionid` = ? AND `key` = ?;',
            array(
                $this->_entity->getEid(), 
                $this->_entity->getRevisionid(), 
                $key,
            )
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:addMetadata - Count check failed'
            );
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['count'] > 0) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:addMetadata - Metadata already exists'
            );
            return false;
        }

        $metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
        $metadata->setEid($this->_entity->getEid());
        // Revision id is not set, since it is not save to the db and hence it
        // do not have a reversionid
        $metadata->setKey($key);
        $metadata->setValue($value);
        $this->_metadata[] = $metadata;
        // The metadata is not saved, since it is not part of the current
        // entity with current revision id
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
    public function saveEntity()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $this->_entity->save();
        $new_revisionid = $this->_entity->getRevisionid();
 
        foreach ($this->_metadata AS $data) {
            $data->setRevisionid($new_revisionid);
            $data->save();
        }  

        foreach ($this->_attributes AS $data) {
            $data->setRevisionid($new_revisionid);
            $data->save();
        }

        $this->_saveBlockedEntities($new_revisionid);
        $this->_saveDisableConsent($new_revisionid);

        return true;	
    }

    /**
     * Load entity.
     *
     * Loads the metadata and attributes associated with the entity.
     *
     * @return bool Return true on success and false on error.
     * @todo Handle error from function calls.  
     */
    public function loadEntity()
    {	
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $this->getMetadata();
        $this->getAttributes();
        $this->getBlockedEntities();
        $this->getDisableConsent();
        $this->getUsers();

        return true;
    }

    /**
     * Get the entity history.
     *
     * Returns an array of entities. One for each revision.
     *
     * @return array|bool An array of sspmod_janus_Entity or FALSE on error
     */
    public function getHistory()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'entity 
            WHERE `eid` = ? 
            ORDER BY `revisionid` DESC', 
            array($this->_entity->getEid())
        );

        if ($st === false) {
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $history = array();
        foreach ($rs AS $data) {
            $entity = new sspmod_janus_Entity($this->_config->getValue('store'));
            $entity->setEid($this->_entity->getEid());
            $entity->setRevisionid($data['revisionid']);
            if (!$entity->load()) {
                SimpleSAML_Logger::error(
                    'JANUS:EntityController:getHistory - Entity could not '
                    . 'load. Eid: '. $this->_entity->getEntityid() . ' - Rid: '
                    . $data['revisionid']
                );
                return false;
            }
            $history[] = $entity;
        }

        return $history;
    }

    /**
     * Get entity
     *
     * Get the entity.
     *
     * @return sspmod_janus_Entity
     */
    public function getEntity()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        return $this->_entity;
    }

    /**
     * Import SP SAML 2.0 metadata.
     *
     * Imports SP SAML 2.0 metadata. The entity id is conpared with that entity
     * id given in the metadata parsed.
     *
     * @param string $metadata SAML 2.0 metadata
     *
     * @return string Return status_metadata_parsed_ok on success and 
     * error_not_valid_saml20, error_metadata_not_parsed or 
     * error_entityid_no_match on error.
     */
    public function importMetadata20SP($metadata)
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');
        assert('$this->_entity->getType() == \'saml20-sp\'');
        assert('is_string($metadata)');

        // Parse metadata
        try {
            $parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);  
        } catch (Exception $e) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - Metadata not valid SAML 2.0'
            );
            return 'error_not_valid_saml20';
        }

        $parsedmetadata = $parser->getMetadata20SP();

        // If metadata was not parsed
        if ($parsedmetadata === null) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - Metadata was not parsed'
            );
            return 'error_metadata_not_parsed';
        }

        // Remove entity descriptor
        unset($parsedmetadata['entityDescriptor']);
        unset($parsedmetadata['metadata-set']);

        // Validate that entity id is the same forimportted metadata and entity
        if ($parsedmetadata['entityid'] != $this->_entity->getEntityid()) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - EntityId does not match'
            );
            return 'error_entityid_no_match';	
        } else {
            unset($parsedmetadata['entityid']);
        }

        // Add metadata fields
        foreach ($parsedmetadata AS $key => $value) {
            if($key == 'AssertionConsumerService') {
                if(is_array($value)) {
                    foreach($value AS $subvalue) {
                        if ($this->hasMetadata($key)) {
                            if (!$this->updateMetadata($key, $subvalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20SP - Metadata field ' . $key 
                                    . ' with value ' . $subvalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($key, $subvalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20SP - Metadata field ' . $key 
                                    . ' with value ' . $subvalue . ' was not added.'
                                );
                            }
                        }
                    }
                }
            } elseif($key == 'SingleLogoutService') {
                if(is_array($value)) {
                    foreach($value AS $subvalue) {
                        if ($this->hasMetadata($key)) {
                            if (!$this->updateMetadata($key, $subvalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20SP - Metadata field ' . $key 
                                    . ' with value ' . $subvalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($key, $subvalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20SP - Metadata field ' . $key 
                                    . ' with value ' . $subvalue . ' was not added.'
                                );
                            }
                        }
                    }
                }
            } else {
                if ($this->hasMetadata($key)) {
                    if (!$this->updateMetadata($key, $value)) {
                        SimpleSAML_Logger::error(
                            'importMetadata20SP - Metadata field ' . $key 
                            . ' with value ' . $value . ' was not added.'
                        );
                    }
                } else {
                    if (!$this->addMetadata($key, $value)) {
                        SimpleSAML_Logger::error(
                            'importMetadata20SP - Metadata field ' . $key 
                            . ' with value ' . $value . ' was not added.'
                        );
                    }
                }
            }
        }

        return 'status_metadata_parsed_ok';
    }

    /**
     * Import IdP SAML 2.0 metadata.
     *
     * Imports IdP SAML 2.0 metadata. The entity id is conpared with that entity id 
     * given in the metadata parsed.
     *
     * @param string $metadata SAML 2.0 metadata
     *
     * @return string Return status_metadata_parsed_ok on success and 
     * error_not_valid_saml20, error_metadata_not_parsed or 
     * error_entityid_no_match on error.
     */
    public function importMetadata20IdP($metadata)
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');
        assert('$this->_entity->getType() == \'saml20-idp\'');
        assert('is_string($metadata)');

        // Parse metadata
        try {
            $parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);  
        } catch (Exception $e) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - Metadata not valid SAML 2.0'
            );
            return 'error_not_valid_saml20';
        }

        $parsedmetadata = $parser->getMetadata20IdP();

        // If metadata was not parsed
        if ($parsedmetadata === null) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - Metadata was not parsed'
            );
            return 'error_metadata_not_parsed';
        }

        // Remove entity descriptor and metadata-set
        unset($parsedmetadata['entityDescriptor']);
        unset($parsedmetadata['metadata-set']);

        // Validate that entity id is the same forimportted metadata and entity
        if ($parsedmetadata['entityid'] != $this->_entity->getEntityid()) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - EntityId does not match'
            );
            return 'error_entityid_no_match';	
        } else {
            unset($parsedmetadata['entityid']);
        }
        
        // Add metadata fields
        foreach ($parsedmetadata AS $key => $value) {
            if ($key == 'name') {
                if (is_array($value)) {
                    foreach ($value AS $langkey => $metadatavalue) {
                        $metadatakey = 'organization:name:' . $langkey;
                        if ($this->hasMetadata($metadatakey)) {
                            if (!$this->updateMetadata($metadatakey, $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($metadatakey, $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        }
                    }
                } else {
                    // Should not happen
                    SimpleSAML_Logger::debug('importMetadata20IdP - name field not array');
                }
            } elseif ($key == 'SingleSignOnService') {
                if (is_array($value)) {
                    if(empty($value)) {
                        continue;
                    }
                    foreach ($value AS $metadatavalue) {
                        if ($this->hasMetadata($key)) {
                            if (!$this->updateMetadata($key, $metadatavalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($key, $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        }
                    }
                } else {
                    // Should not happen
                    SimpleSAML_Logger::debug('importMetadata20IdP - SingleSignOnService field not array');
                }
            } elseif ($key == 'SingleLogoutService') {
                if (is_array($value)) {
                    if(empty($value)) {
                        continue;
                    }
                    foreach ($value AS $metadatavalue) {
                        if ($this->hasMetadata($key)) {
                            if (!$this->updateMetadata($key, $metadatavalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($key, $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        }
                    }
                } else {
                    // Should not happen
                    SimpleSAML_Logger::debug('importMetadata20IdP - SingleLogoutService field not array');
                }
            } elseif ($key == 'ArtifactResolutionService') {
                if (is_array($value)) {
                    if(empty($value)) {
                        continue;
                    }
                    foreach ($value AS $metadatavalue) {
                        if ($this->hasMetadata($key)) {
                            if (!$this->updateMetadata($key, $metadatavalue['Location'])) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata($key, $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        }
                    }
                } else {
                    // Should not happen
                    SimpleSAML_Logger::debug('importMetadata20IdP - ArtifactResolutionService field not array');
                }
            } elseif ($key == 'description') {
                // Not user in SAML2 metadata. Only used in SSP flatfile metadata
                continue;
            } elseif ($key == 'certFingerprint') {
                if (is_array($value)) {
                    foreach ($value AS $metadatakey => $metadatavalue) {
                        if ($this->hasMetadata('certFingerprint')) {
                            if (!$this->updateMetadata('certFingerprint', $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        } else {
                            if (!$this->addMetadata('certFingerprint', $metadatavalue)) {
                                SimpleSAML_Logger::error(
                                    'importMetadata20IdP - Metadata field ' . $key 
                                    . ' with value ' . $metadatavalue . ' was not added.'
                                );
                            }
                        }
                    }
                } else {
                    // Should not happen
                    continue;
                }
            } else {
                if ($this->hasMetadata($key)) {
                    if (!$this->updateMetadata($key, $value)) {
                        SimpleSAML_Logger::error(
                            'importMetadata20IdP - Metadata field ' . $key 
                            . ' with value ' . $value . ' was not added.'
                        );
                    }
                } else {
                    if (!$this->addMetadata($key, $value)) {
                        SimpleSAML_Logger::error(
                            'importMetadata20IdP - Metadata field ' . $key 
                            . ' with value ' . $value . ' was not added.'
                        );
                    }
                }
            }	
        }

        return 'status_metadata_parsed_ok';
    }

    /**
     * Udate attribute
     *
     * Update the given attribute.
     *
     * @param string $key   Attribute key
     * @param atring $value Attribute value
     *
     * @return bool Return TRUE on success and FLASE on error
     */ 
    public function updateAttribute($key, $value)
    {
        assert('is_string($key);');	
        assert('is_string($value);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_attributes)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $update = false;

        foreach ($this->_attributes AS &$data) {
            if ($data->getKey() == $key) {
                $data->setValue($value);
                $update = true;
            }
        }

        return $update;
    }

    /**
     * Has metadata
     *
     * Ask if the given metadata exist
     *
     * @param string $key  Metadata key
     *
     * @return bool Return TRUE if the Metadata exists. FALSE otherwise
     */
    public function hasMetadata($key)
    {
        assert('is_string($key);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        foreach ($this->_metadata AS $data) {
            if ($data->getKey() === $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update metadata
     *
     * Update the given metadata.
     *
     * @param string $key   Metadata key
     * @param atring $value Metadata value
     *
     * @return bool Return TRUE on success and FLASE on error
     */ 
    public function updateMetadata($key, $value)
    {
        assert('is_string($key);');	
        assert('is_string($value);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $update = false;

        foreach ($this->_metadata AS &$data) {
            if ($data->getKey() === $key && $data->getValue() !== $value) {
                $data->setValue($value);
                $update = true;
            }
        }

        return $update;
    }

    /**
     * Remove metadata
     *
     * Remove the give metadata fom the EntityController. The metadata will only 
     * be removed from the entity if you call saveEntity().
     *
     * @param string $key Metadata key
     *
     * @return bool Returns TRUE on success and FALSE on error
     */
    public function removeMetadata($key)
    {
        assert('is_string($key);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $update = false;

        foreach ($this->_metadata AS $index => &$data) {
            if ($data->getKey() == $key) {
                unset($this->_metadata[$index]);
                $update = true;
            }
        }

        return $update;
    }

    /**
     * Remove attribute
     *
     * Remove the give attribute from the EntityController. The attribute will only 
     * be removed from the entity if you call saveEntity().
     *
     * @param string $key Attribute key
     *
     * @return bool Returns TRUE on success and FALSE on error
     */
    public function removeAttribute($key)
    {
        assert('is_string($key);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_attributes)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $update = false;

        foreach ($this->_attributes AS $index => &$data) {
            if ($data->getKey() == $key) {
                unset($this->_attributes[$index]);
                $update = true;
            }
        }

        return $update;
    }

    /**
     * Add an entity to the current entitys blocked list
     *
     * If the blocked entity is alreade sey, the methos will return true.
     *
     * @param string $remoteentityid Entity id of blocked entity
     *
     * @return true Return true on success
     * @since Method available since Release 1.0.0
     */
    public function addBlockedEntity($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        if (!array_key_exists($remoteentityid, $this->_blocked)) {
            $this->_blocked[$remoteentityid] 
                = array('remoteentityid' => $remoteentityid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * remove an entity from the current entity blocked list.
     *
     * @param string $remoteentityid The entity to be removed
     *
     * @return true Returns true on success
     * @since Method available since Release 1.0.0
     */
    public function removeBlockedEntity($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        unset($this->_blocked[$remoteentityid]);
        $this->_modified = true;

        return true;
    }

    /**
     * Load the blocked entities from the database
     *
     * Fetches the entity id of the blocked entities from the database. The 
     * revision id needs to be set othervise no entities will be returned from 
     * the database.
     *
     * @return bool Return true on success and false on error
     * @since Method available since Release 1.0.0
     */
    private function _loadBlockedEntities()
    {
        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'blockedEntity 
            WHERE `entityid` = ? AND `revisionid` = ?;',
            array($this->_entity->getEntityid(), $this->_entity->getRevisionid())
        );

        if ($st === false) {
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        $this->_blocked = array();

        foreach ($row AS $data) {
            $this->_blocked[$data['remoteentityid']] = $data;
        }
        $this->_modified = false;

        return true;
    }

    /**
     * Set the allowedAll flag for the current entity
     *
     * When setting the allowedAll flag all blocked entities of the current
     * entity is removed.
     *
     * @param string $allowedall AllowedAll flag, 'yes'/'no'
     *
     * @return bool True if the allowedAll flag was changed. Othervise false. 
     * @since      Method available since Release 1.0.0
     */
    public function setAllowedAll($allowedall)
    {
        $return = $this->_entity->setAllowedall($allowedall);
        if ($allowedall === 'yes') {
            $this->_blocked = array();
            $this->_modified = true;
            return true;
        }
        return $return;
    }

    /**
     * Save all blocked entities of the current entity to the database
     *
     * All blocked entities are saved to database together with a timestamp and 
     * the IP address from where the call was made from.
     *
     * @param string $revision The revision id
     *
     * @return bool True if there was any thing to save nad false othervise
     * @since Method available since Release 1.0.0
     * @todo Why is revision id pase to this method??
     */
    private function _saveBlockedEntities($revision)
    {
        if ($this->_modified) {
            foreach ($this->_blocked AS $blocked) {
                $st = $this->execute(
                    'INSERT INTO '. self::$prefix .'blockedEntity (
                    `entityid`, `revisionid`, `remoteentityid`, `created`, `ip`)
                    VALUES (?, ?, ?, ?, ?);', 
                    array(
                        $this->_entity->getEntityid(), 
                        $revision, 
                        $blocked['remoteentityid'], 
                        date('c'), 
                        $_SERVER['REMOTE_ADDR'],
                    )
                );

                if ($st === false) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * return users that have permission to edit the entity
     *
     * @return array List of user emails. False onn error
     * @since      Method available since Release 1.0.0
     * @todo Throw exception if user do not load
     */
    public function getUsers()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_users)) {
            if (!$this->_loadUsers()) {
                return false;
            }
        }
        return $this->_users;
    }

    /**
     * Retrive user with persimission to edit the entity from database
     *
     * @return bool True on success and false on error
     * @since      Method available since Release 1.0.0 
     */
    private function _loadUsers()
    {
        $st = $this->execute(
            'SELECT `userid` 
            FROM '. self::$prefix .'hasEntity t1, '. self::$prefix .'user t2 
            WHERE t1.`eid` = ? AND t1.`uid` = t2.`uid`;',
            array($this->_entity->getEid())
        );

        if ($st === false) {
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);

        $this->_users = array();

        foreach ($row AS $data) {
            $this->_users[$data['userid']] = true;
        }
        $this->_modified = false;

        return true;
    }


    public function getMetaArray() {
        if (empty($this->_metadata)) {
            if (!$this->_loadMetadata()) {
                return false;
            }
        }

        $metaArray = array();
        $metaArray['contacts'] = array();
        $metaArray['organization'] = array();
        foreach($this->_metadata AS $data) {
            if(preg_match('/entity:name:([\w]{2})$/', $data->getKey(), $matches)) {
            	$metaArray['name'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/entity:description:([\w]{2})$/', $data->getKey(), $matches)) {
                $metaArray['description'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/entity:url:([\w]{2})$/', $data->getKey(), $matches)) {
                $metaArray['url'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/organization:name:([\w]{2})$/', $data->getKey(), $matches)) {
                $metaArray['organization']['name'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/organization:description:([\w]{2})$/', $data->getKey(), $matches)) {
                $metaArray['organization']['description'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/organization:url:([\w]{2})$/', $data->getKey(), $matches)) {
                $metaArray['organization']['url'][$matches[1]] = $data->getValue();
            } elseif(preg_match('/contacts:name/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['name'] = $data->getValue();
            } elseif(preg_match('/contacts:emailAddress/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['emailAddress'] = $data->getValue();
            } elseif(preg_match('/contacts:givenName/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['givenName'] = $data->getValue();
            } elseif(preg_match('/contacts:surName/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['surName'] = $data->getValue();
            } elseif(preg_match('/contacts:contactType/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['contactType'] = $data->getValue();
            } elseif(preg_match('/contacts:company/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['company'] = $data->getValue();
            } elseif(preg_match('/contacts:telephoneNumber/', $data->getKey(), $matches)) {
                $metaArray['contacts'][1]['telephoneNumber'] = $data->getValue();
            } else {
                $metaArray[$data->getKey()] = $data->getValue();
            }
        }

        if(empty($metaArray['organization'])) {
            unset($metaArray['organization']);
        }
        if(empty($metaArray['contacts'])) {
            unset($metaArray['contacts']);
        }

        $metaArray['entityid'] = $this->_entity->getEntityid();
        $entity_type = $this->_entity->getType();
        $metaArray['metadata-set'] = $this->_entity->getType().'-remote';

        if (!array_key_exists('NameIDFormat', $metaArray)) {
            if($entity_type == 'saml20-idp' || $entity_type == 'saml20-sp') {
                $metaArray['NameIDFormat'] = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
            } else if($entity_type == 'shib13-idp' || $entity_type == 'shib13-idp') {
                $metaArray['NameIDFormat'] = 'urn:mace:shibboleth:1.0:nameIdentifier';
            }
        } 

        if(!empty($this->_attributes)) {
            foreach($this->_attributes AS $attr) {
                $metaArray['attributes'][] = $attr->getKey();
            }
        }

        return $metaArray;
    }
    
    public function addDisableConsent($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        if (!array_key_exists($remoteentityid, $this->_disableConsent)) {
            $this->_disableConsent[$remoteentityid]
                = array('remoteentityid' => $remoteentityid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    public function removeDisableConsent($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        unset($this->_disableConsent[$remoteentityid]);
        $this->_modified = true;

        return true;
    }
    
    public function getDisableConsent()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_disableConsent)) {
            if (!$this->_loadDisableConsent()) {
                return false;
            }
        }
        return $this->_disableConsent;
    }

    private function _loadDisableConsent()
    {
        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'disableConsent 
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($this->_entity->getEid(), $this->_entity->getRevisionid())
        );

        if ($st === false) {
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        $this->_disableConsent = array();

        foreach ($row AS $data) {
            $this->_disableConsent[$data['remoteentityid']] = $data;
        }
        $this->_modified = false;

        return true;
    }
    
    private function _saveDisableConsent($revision)
    {
        if ($this->_modified) {
            foreach ($this->_disableConsent AS $disable) {
                $st = $this->execute(
                    'INSERT INTO '. self::$prefix .'disableConsent (
                    `eid`, `revisionid`, `remoteentityid`, `created`, `ip`)
                    VALUES (?, ?, ?, ?, ?);',
                    array(
                        $this->_entity->getEid(),
                        $revision,
                        $disable['remoteentityid'],
                        date('c'),
                        $_SERVER['REMOTE_ADDR'],
                    )
                );

                if ($st === false) {
                    return false;
                }
            }
        }
        return true;
    }

    public function clearConsent()
    {
        $this->_disableConsent = array();
        $this->_modified = true;

        return true;
    } 
    
    public function setMetadataURL($url)
    {
        assert('is_string($url)');

        if($this->_entity->getMetadataURL() != $url)
        {
            $this->_entity->setMetadataURL($url);
            $this->_modified = true;
            return true;
        }
        return false;
    }
}
?>
