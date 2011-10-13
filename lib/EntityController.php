<?php
/**
 * Controller for entities
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Ivo Jansch <ivo@ibuildings.nl>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
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
 * @author     Ivo Jansch <ivo@ibuildings.nl>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
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

    private $_blocked = array();
    private $_blockedLoaded = false;

    private $_allowed = array();
    private $_allowedLoaded = false;

    private $_users;

    private $_modified = false;

    private $_arp;

    /**
     * Class constructor.
     *
     * Constructs a EntityController object.
     *
     * @param SimpleSAML_Configuration $config Global SSP configuration
     */
    public function __construct(SimpleSAML_Configuration $config)
    {
        parent::__construct($config->getValue('store'));
        $this->_config = $config;
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
            // Clear cached metadata if we're dealing with a new entity
            $this->_metadata = null;

            // Create a new entity
            $this->_entity
                = new sspmod_janus_Entity($this->_config);
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
        } else if (is_string($entity)) {
            // Clear cached metadata if we're dealing with a new entity
            $this->_metadata = null;

            // Create a new entity
            $this->_entity
                = new sspmod_janus_Entity($this->_config);
            $this->_entity->setEntityid($entity);
            // If a revisionid is parsed
            if (isset($revisionid)) {
                assert('ctype_digit($revisionid);');
                $this->_entity->setRevisionid($revisionid);
            }
            // Load entity information
            if (!$this->_entity->load()) {
                SimpleSAML_Logger::error(
                    'JANUS:EntityController:setEntity - Entity could not load.'
                    . ' Entityid: '. $entity . ' - Rid: '. $revisionid
                );
                return false;
            }
        } else {
            $this->_entity = null;
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

        $mb = new sspmod_janus_MetadatafieldBuilder(
            $this->_config->getArray('metadatafields.' . $this->_entity->getType())
        );
        $definitions = $mb->getMetadatafields();

        foreach ($rs AS $row) {
            $metadata = new sspmod_janus_Metadata($this->_config->getValue('store'));
            $metadata->setEid($row['eid']);
            $metadata->setRevisionid($row['revisionid']);
            $metadata->setKey($row['key']);
            if (isset($definitions[$row['key']])) {
                $metadata->setDefinition($definitions[$row['key']]);
            }
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

        if (empty($this->_blocked) && !$this->_blockedLoaded) {
            /*
             * Only load if we haven't loaded it already; otherwise we keep
             * loading repeatedly if the result is empty.
             */
            if (!$this->_loadBlockedEntities()) {
                return false;
            }
            $this->_blockedLoaded = true;
        }
        return $this->_blocked;
    }

    /**
     * Get all allowed entities for the entity
     *
     * @return array All allowed entities
     */
    public function getAllowedEntities()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_allowed) && !$this->_allowedLoaded) {
            if (!$this->_loadAllowedEntities()) {
                return false;
            }
            $this->_allowedLoaded = true;
        }
        return $this->_allowed;
    }

    /**
     * Retrive the ARP from the database
     *
     * @return true Aæways return true
     */
    private function _loadArp()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if ($this->_entity->getArp() == '0') {
            $this->_arp = null;
        } else {
            $this->_arp = new sspmod_janus_ARP();
            $this->_arp->setAid($this->_entity->getArp());
            $this->_arp->load();
        }

        return true;
    }

    /**
     * Get the ARP for the entity
     *
     * @return sspmod_janus_ARP|false|null The ARP, false on error and null if 
     *                                     no ARP is selected   
     */
    public function getArp()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_arp)) {
            if (!$this->_loadArp()) {
                return false;
            }
        }
        return $this->_arp;
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
        //assert('is_string($value);');
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $allowedfields = array();
        $mb = new sspmod_janus_MetadatafieldBuilder(
            $this->_config->getArray('metadatafields.' . $this->_entity->getType())
        );
        $allowedfields = $mb->getMetadatafields();
        
        // Check if metadata is allowed
        if (!array_key_exists($key, $allowedfields)) {
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
        $this->_modified = true;
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

        $this->_saveBlockedEntities($new_revisionid);
        $this->_saveAllowedEntities($new_revisionid);
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
        $this->getArp();
        $this->getBlockedEntities();
        $this->getAllowedEntities();
        $this->getDisableConsent();
        $this->getUsers();

        $this->_modified = false;

        return true;
    }

    /**
     * Get the entity history.
     *
     * Returns an array of entities. One for each revision.
     *
     * @param int $lower_limit The lower limit from which get revisions
     * @param int $upper_limit The upper limit up to which get revisions
     *
     * @return array|bool An array of sspmod_janus_Entity or FALSE on error
     */
    public function getHistory($lower_limit = null, $upper_limit = null)
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if ($lower_limit !== null || $upper_limit !== null) {
            $limit_clause = ' LIMIT';
            if ($lower_limit !== null) {
                $limit_clause = $limit_clause . ' ' . $lower_limit;
            }
            if ($upper_limit !== null) {
                $separator = $limit_clause === null ? ' ' : ', ';
                $limit_clause = $limit_clause . $separator . $upper_limit;
            }
        } else {
            $limit_clause = '';
        }

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'entity 
            WHERE `eid` = ? 
            ORDER BY `revisionid` DESC' . $limit_clause,
            array($this->_entity->getEid())
        );

        if ($st === false) {
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $history = array();
        foreach ($rs AS $data) {
            $entity = new sspmod_janus_Entity($this->_config);
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
     * Get the entity history size.
     *
     * Returns a intenger with the number of history entries
     *
     * @return int Number of history entries
     */
    public function getHistorySize()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $st = $this->execute(
            'SELECT COUNT(*) as size
            FROM ' . self::$prefix . 'entity
            WHERE `eid` = ?',
            array($this->_entity->getEid())
        );

        if ($st === false) {
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        $size = 0;
        foreach ($rs as $data) {
            $size = $data['size'];
        }

        return $size;
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
     * @param string  $metadata SAML 2.0 metadata
     * @param boolean &$updated Output value. True if something was changed
     *
     * @return string Return status_metadata_parsed_ok on success and 
     * error_not_valid_saml20, error_metadata_not_parsed or 
     * error_entityid_no_match on error.
     */
    public function importMetadata20SP($metadata, &$updated)
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');
        assert('$this->_entity->getType() == \'saml20-sp\'');
        assert('is_string($metadata)');

        // Parse metadata
        try {
            $parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);
            $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($metadata);
        } catch (Exception $e) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - Metadata not valid SAML 2.0' .
                var_export($e, true)
            );
            return 'error_not_valid_saml20';
        }

        SimpleSAML_Logger::debug('Entities Found: '. count($entities));
        if (count($entities) > 1) {

            // We found multiple entities, So we have to loop through them
            // in order to select the entity ID which we want to import
            foreach($entities as $entityId => $parser) {

                if ($entityId === $this->_entity->getEntityid()) {
                    SimpleSAML_Logger::debug('Matching EntityIDs found for: '. $entityId);

                    // Import metadata
                    SimpleSAML_Logger::debug('Processing EntityID: '. $entityId);
                    return self::_importMetadata20SP($parser, $updated);
                }
            }
            // Apparently the entity was not found in supplied metadata, Log error
            SimpleSAML_Logger::error(
                'importMetadata20SP - EntityId not found'
            );

            return 'error_entityid_not_found';

        } else if (count($entities) == 1) {
            $parser = $entities[key($entities)];
            return self::_importMetadata20SP($parser, $updated);
        } else {
            // The parsed metadata contains no entities
            SimpleSAML_Logger::error(
                'importMetadata20SP - EntityId not found'
            );

            return 'error_entityid_not_found';
        }
    }

    private function _importMetadata20SP($parser, &$updated)
    {
        $parsedmetadata = $parser->getMetadata20SP();

        $parsedmetadata = self::reparseMetadata($parsedmetadata);

        // If metadata was not parsed
        if ($parsedmetadata === null) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - Metadata was not parsed'
            );
            return 'error_metadata_not_parsed';
        }

        if (isset($parsedmetadata['expire']) && $parsedmetadata['expire'] < time()) {
            SimpleSAML_Logger::error(
                'importMetadata20SP - Metadata was not parsed due expiration'
            );
            return 'error_metadata_not_parsed_due_expiration';
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

        $parsedmetadata = self::arrayFlattenSep(':', $parsedmetadata);

        if (isset($parsedmetadata['keys:0:X509Certificate'])) {
            $parsedmetadata['certData'] = $parsedmetadata['keys:0:X509Certificate'];
        }

        foreach ($parsedmetadata AS $key => $value) {        
            if ($this->hasMetadata($key)) {
                if (!$this->updateMetadata($key, $value)) {
                    SimpleSAML_Logger::info(
                        'importMetadata20SP - Metadata field ' . $key 
                        . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $updated = true;
                }
            } else {
                if (!$this->addMetadata($key, $value)) {
                    SimpleSAML_Logger::info(
                        'importMetadata20SP - Metadata field ' . $key 
                        . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $updated = true;
                }
            }
        }

        return 'status_metadata_parsed_ok';
    }

    /**
     * Reparse metadata to correct the contact persomn metadata
     *
     * @param array $parsedmetadata Array of metadata as returned by SSP
     *
     * @return array Array of metadata
     */
    public static function reparseMetadata($parsedmetadata)
    {
        /*
         * Janus only support one telephone / emailAddress per contact so I geti
         * the first
         */
        if (isset($parsedmetadata['contacts'])) {
            for ($i=0;$i<count($parsedmetadata['contacts']);$i++) {
                if (isset($parsedmetadata['contacts'][$i]['emailAddress'])) {
                    $parsedmetadata['contacts'][$i]['emailAddress']
                        = $parsedmetadata['contacts'][$i]['emailAddress'][0];
                }
                if (isset($parsedmetadata['contacts'][$i]['telephoneNumber'])) {
                    $parsedmetadata['contacts'][$i]['telephoneNumber']
                        = $parsedmetadata['contacts'][$i]['telephoneNumber'][0];
                }
            }
        }

        return $parsedmetadata;
    }

    /**
     * Flatten an array to only one levet using the seperator
     *
     * @param string $sep   The seperator to flatten the array over
     * @param array  $array The array to be flattend
     *
     * @return array The flattend array to one level 
     */
    public static function arrayFlattenSep($sep, $array)
    {
        $result = array();
        $stack = array();
        array_push($stack, array("", $array));

        while (count($stack) > 0) {
            list($prefix, $array) = array_pop($stack);

            foreach ($array as $key => $value) {
                $new_key = $prefix . strval($key);

                if (is_array($value)) {
                    array_push($stack, array($new_key . $sep, $value));
                } else {
                    $result[$new_key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Import IdP SAML 2.0 metadata.
     *
     * Imports IdP SAML 2.0 metadata. The entity id is conpared with that entity id 
     * given in the metadata parsed.
     *
     * @param string $metadata SAML 2.0 metadata
     * @param bool   &$updated Whether the entity was updated
     *
     * @return string Return status_metadata_parsed_ok on success and 
     * error_not_valid_saml20, error_metadata_not_parsed or 
     * error_entityid_no_match on error.
     */
    public function importMetadata20IdP($metadata, &$updated)
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');
        assert('$this->_entity->getType() == \'saml20-idp\'');
        assert('is_string($metadata)');

        // Parse metadata
        try {
            $parser = SimpleSAML_Metadata_SAMLParser::parseString($metadata);
            $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($metadata);
        } catch (Exception $e) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - Metadata not valid SAML 2.0' . 
                var_export($e, true)
            );
            return 'error_not_valid_saml20';
        }
        SimpleSAML_Logger::debug('Entities Found: '. count($entities));
        if (count($entities) > 1) {

            // We found multiple entities, So we have to loop through them
            // in order to select the entity ID which we want to import
            foreach($entities as $entityId => $parser) {

                if ($entityId === $this->_entity->getEntityid()) {
                    SimpleSAML_Logger::debug('Matching EntityIDs found for: '. $entityId);

                    // Import metadata
                    SimpleSAML_Logger::debug('Processing EntityID: '. $entityId);
                    return self::_importMetadata20IdP($parser, $updated);
                }
            }
            // Apparently the entity was not found in supplied metadata, Log error
            SimpleSAML_Logger::error(
                'importMetadata20IdP - EntityId not found'
            );

            return 'error_entityid_not_found';

        } else if (count($entities) == 1) {
            $parser = $entities[key($entities)];
            return self::_importMetadata20IdP($parser, $updated);
        } else {
            // The parsed metadata contains no entities
            SimpleSAML_Logger::error(
                'importMetadata20IdP - EntityId not found'
            );

            return 'error_entityid_not_found';
        }
    }

    private function _importMetadata20IdP($parser, &$updated)
    {
        $parsedmetadata = $parser->getMetadata20IdP();

        $parsedmetadata = self::reparseMetadata($parsedmetadata);

        // If metadata was not parsed
        if ($parsedmetadata === null) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - Metadata was not parsed'
            );
            return 'error_metadata_not_parsed';
        }

        if (isset($parsedmetadata['expire']) && $parsedmetadata['expire'] < time()) {
            SimpleSAML_Logger::error(
                'importMetadata20IdP - Metadata was not parsed due expiration'
            );
            return 'error_metadata_not_parsed_due_expiration';
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

        $parsedmetadata = self::arrayFlattenSep(':', $parsedmetadata);

        foreach ($parsedmetadata AS $key => $value) {        
            if ($this->hasMetadata($key)) {
                if (!$this->updateMetadata($key, $value)) {
                    SimpleSAML_Logger::info(
                        'importMetadata20IdP - Metadata field ' . $key 
                        . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $updated = true;
                }
            } else {
                if (!$this->addMetadata($key, $value)) {
                    SimpleSAML_Logger::info(
                        'importMetadata20IdP - Metadata field ' . $key 
                        . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $updated = true;
                }
            }
        }

        return 'status_metadata_parsed_ok';
    }

    /**
     * Has metadata
     *
     * Ask if the given metadata exist
     *
     * @param string $key Metadata key
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
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $update = false;

        foreach ($this->_metadata AS &$data) {
            if ($data->getKey() === $key && $data->getValue() != $value) {
                $data->setValue($value);
                $this->_modified = true;
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
                $this->_modified = true;
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

        if (isset($this->_blocked[$remoteentityid])) {
            unset($this->_blocked[$remoteentityid]);
            $this->_modified = true;
        }
        return true;
    }

    /**
     * Remove all blockedEntities
     * 
     * @return void
     * @since Method available since Release 1.8.0
     */
    public function clearBlockedEntities()
    {
        if (count($this->_blocked)) {
            $this->_blocked = array();
            $this->_modified = true;
        }
    }

    /**
     * Add an entity to the current entitys allowed list
     *
     * If the blocked entity is alreade sey, the methos will return true.
     *
     * @param string $remoteentityid Entity id of blocked entity
     *
     * @return true Return true on success
     * @since Method available since Release 1.8.0
     */
    public function addAllowedEntity($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        if (!array_key_exists($remoteentityid, $this->_allowed)) {
            $this->_allowed[$remoteentityid] 
                = array('remoteentityid' => $remoteentityid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * remove an entity from the current entity allowed list.
     *
     * @param string $remoteentityid The entity to be removed
     *
     * @return true Returns true on success
     * @since Method available since Release 1.8.0
     */
    public function removeAllowedEntity($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        if (isset($this->_allowed[$remoteentityid])) {
            unset($this->_allowed[$remoteentityid]);
            $this->_modified = true;
        }
        return true;
    }

    /**
     * Remove all allowedEntities
     * 
     * @return void
     * @since Method available since Release 1.8.0
     */
    public function clearAllowedEntities()
    {
        if (count($this->_allowed)) {
            $this->_allowed = array();
            $this->_modified = true;
        }
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
        return $this->_loadLinkedEntities('blocked');
    }

    /**
     * Load the allowed entities from the database
     *
     * Fetches the entity id of the allowed entities from the database. The 
     * revision id needs to be set othervise no entities will be returned from 
     * the database.
     *
     * @return bool Return true on success and false on error
     * @since Method available since Release 1.8.0
     */
    private function _loadAllowedEntities()
    {
        return $this->_loadLinkedEntities('allowed');
    }

    /**
     * Get the blocked/allowed entities from the database
     *
     * @param String $type must be 'blocked' or 'allowed'
     *
     * @return bool True on success and false on error
     */
    private function _loadLinkedEntities($type)
    {
        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix . $type . 'Entity 
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($this->_entity->getEid(), $this->_entity->getRevisionid())
        );

        if ($st === false) {
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);

        $this->{'_'.$type} = array();

        foreach ($row AS $data) {
            $this->{'_'.$type}[$data['remoteentityid']] = $data;
        }

        return true;
    }

    /**
     * Set the allowedAll flag for the current entity
     *
     * @param string $allowedall allowedAll flag, 'yes'/'no'
     *
     * @return bool True if the allowAll flag was changed. Othervise false. 
     * @since      Method available since Release 1.0.0
     */
    public function setAllowedAll($allowedall)
    {
        $return = $this->_entity->setAllowedAll($allowedall);

        // If $return = true, it means it changed.
        if ($return) {
            $this->_modified = true;
        }
        if ($allowedall === 'yes') {
            $this->_blocked = array();
            $this->_allowed = array();
        }
        return $return;
    }

    /**
     * Get the allowedAll flag for the current entity
     * 
     * @return bool True if the entity's allowedAll is true, false if not.
     */
    public function getAllowedAll()
    {
        return $this->_entity->getAllowedAll();
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
        return $this->_saveLinkedEntities($revision, "blocked");
    }

    /**
     * Save the allowed entities to the database
     *
     * @param int $revision The revision
     *
     * @return void|false void on success and false on error
     */
    private function _saveAllowedEntities($revision)
    {
        return $this->_saveLinkedEntities($revision, "allowed");
    }

    /**
     * Save the allowed/blocked entities to the database
     *
     * @param int    $revision The revision
     * @param string $type     The type of entities
     *
     * @return void|false void on success and false on error
     */
    private function _saveLinkedEntities($revision, $type)
    {    
        if ($this->_modified) {
            foreach ($this->{'_'.$type} AS $linked) {
                $st = $this->execute(
                    'INSERT INTO '. self::$prefix . $type . 'Entity (
                    `eid`, `revisionid`, `remoteentityid`, `created`, `ip`)
                    VALUES (?, ?, ?, ?, ?);', 
                    array(
                        $this->_entity->getEid(), 
                        $revision, 
                        $linked['remoteentityid'], 
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

        return true;
    }

    /**
     * Merge to array recursivly. 
     *
     * This function will merges two array together. This function will also 
     * merge numeric keys as opposed to array_merge_recursive which will not 
     * merge numeric keys.
     *
     * @param array $array1 The first array
     * @param array $array2 The second array
     *
     * @return array The merged version of the two input arrays
     * @since        Method available since Release 1.6.0 
     */
    public static function arrayMergeRecursiveFixed($array1, $array2)
    {
        if (is_array($array1)) {
            if (is_array($array2)) {
                foreach ($array2 AS $key => $val) {
                    if (   isset($array1[$key]) 
                        && is_array($val) 
                        && is_array($array1[$key])
                    ) {
                        $array1[$key] 
                            = self::arrayMergeRecursiveFixed($array1[$key], $val);
                    } else {
                        while (isset($array1[$key])) {
                            $key++;
                        }
                        $array1[$key] = $val;
                    }
                }
            }
        } else if (is_array($array2)) {
            $array1 = $array2;
        } else {
            $array1 = Array();
        }
        return $array1;
    }

    /**
     * Get all metadata for the entity
     *
     * @return false|array Array with metadata or false on error
     */
    public function getMetaArray()
    {
        if (empty($this->_metadata)) {
            if (!$this->_loadMetadata()) {
                return false;
            }
        }
        if (empty($this->_arp)) {
            if (!$this->_loadArp()) {
                return false;
            }
        }

        $metaArray = array();

        foreach ($this->_metadata AS $data) {
            if (strpos($data->getKey(), ':')) {
                $keys = explode(':', $data->getKey());
                $val = $data->getValue();
                while (!empty($keys)) {
                    $array = array();
                    $newkey = array_pop($keys);
                    $array[$newkey] = $val;
                    $val = $array;
                }
                $metaArray = self::arrayMergeRecursiveFixed($array, $metaArray);
            } else {
                $metaArray[$data->getKey()] = $data->getValue();
            }
        }

        $metaArray['entityid'] = $this->_entity->getEntityid();

        /*
         * The expiration field in the entity table is not for metadata 
         * expiration, but for telling when the entity can no longer be accessed 
         * via JANUS. 
         * To set expiration on metadata a metadata field called expiration 
         * should be set
         */
        /* 
        $expiration = $this->getEntity()->getExpiration();
        if ($expiration) {
            $metaArray['expire'] = SimpleSAML_Utilities::parseSAML2Time($expiration);
        }
         */
        $entity_type = $this->_entity->getType();
        $metaArray['metadata-set'] = $this->_entity->getType().'-remote';

        if (!array_key_exists('NameIDFormat', $metaArray)) {
            if ($entity_type == 'saml20-idp' || $entity_type == 'saml20-sp') {
                $metaArray['NameIDFormat'] 
                    = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
            } else if ($entity_type == 'shib13-idp' 
                || $entity_type == 'shib13-idp'
            ) {
                $metaArray['NameIDFormat'] 
                    = 'urn:mace:shibboleth:1.0:nameIdentifier';
            }
        } 

        if ($entity_type == 'saml20-sp') {
            if (!is_null($this->_arp)) {
                $metaArray['attributes'] = array();
                foreach ($this->_arp->getAttributes() AS $attr) {
                    $metaArray['attributes'][] = $attr;
                }
            } else {
                $defaultarp 
                    = $this->_config->getArray('entity.defaultarp', 'NOTDEFINED');
                if ($defaultarp != 'NOTDEFINED') {
                    $metaArray['attributes'] = $defaultarp;
                }
            }
        }
        if (!isset($metaArray['name'])) {
            $metaArray['name']['en'] = $this->_entity->getEntityid();
        }

        return $metaArray;
    }

    /**
     * Disable consent for remote entity
     *
     * @param string $remoteentityid Entityid of remote entity
     *
     * @return bool True on success and false on error
     */
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

    /**
     * Enable consent for remote entity
     *
     * @param string $remoteentityid Entityid of remote entity
     *
     * @return true Always return true
     */
    public function removeDisableConsent($remoteentityid)
    {
        assert('is_string($remoteentityid)');

        if (isset($this->_disableConsent[$remoteentityid])) {
            unset($this->_disableConsent[$remoteentityid]);
            $this->_modified = true;
        }
        return true;
    }

    /**
     * Retrive all remote entities with consent disabled
     *
     * @return array Remote entities
     */
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

    /**
     * Get disabled consent from database
     *
     * @return bool True on success and false on error
     */
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

        return true;
    }

    /**
     * Save disable consent to database
     *
     * @param int $revision The current revision number
     *
     * @return bool True on success and false on error
     */
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

    /**
     * Enable consent for all remote entities
     *
     * @return true Always return true
     */
    public function clearConsent()
    {
        if (count($this->_disableConsent)>0) {
            $this->_disableConsent = array();
            $this->_modified = true;
        }
        return true;
    } 

    /**
     * set the metadata URL
     *
     * @param string $url Metadata URL
     *
     * @return bool True on success and false on error
     */
    public function setMetadataURL($url)
    {
        assert('is_string($url)');

        if ($this->_entity->getMetadataURL() != $url) {
            $this->_entity->setMetadataURL($url);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Set the ARP for the entity
     *
     * @param sspmod_janus_ARP $arp The ARP
     *
     * @return void
     */
    public function setArp($arp)
    {
        $this->_entity->setArp($arp);
    }

    /**
     * Create a certificate object based on the certData field.
     *
     * @throws sspmod_janus_Exception_NoCertData
     * @throws sspmod_janus_OpenSsl_Certificate_Exception_NotAValidPem
     * @return sspmod_janus_OpenSsl_Certificate
     */
    public function getCertificate()
    {
        $metadata = $this->getMetaArray();
        if (!isset($metadata['certData']) || trim($metadata['certData'])==="") {
            throw new sspmod_janus_Exception_NoCertData("Unable to create certificate object, certData metadata missing!");
        }
        return sspmod_janus_CertificateFactory::create($metadata['certData']);
    }

    /**
     * Get the validUntil and cacheDuration for the current entity.
     *
     * Example of return value:
     * array(
     *   'validUntil' => 1318437451,
     *   'cacheUntil' => 1318435651,
     * )
     *
     * Returns false if it fails to load the entity.
     *
     * @return array|bool
     */
    public function getMetadataCaching()
    {
        $currentEntity = $this->getEntity();
        $st = $this->execute(
            'SELECT metadata_valid_until, metadata_cache_until
            FROM '. self::$prefix .'entity
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($currentEntity->getEid(), $currentEntity->getRevisionid())
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:EntityController:_loadMetadata - Metadata could not load.'
            );
            return false;
        }
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        return array(
            'validUntil' => strtotime($rs[0]['metadata_valid_until']),
            'cacheUntil' => strtotime($rs[0]['metadata_cache_until'])
        );
    }

    /**
     * Set the time until cache of metadata is valid and the time the metadata expires.
     *
     * @param int $validUntil Unix timestamp
     * @param int $cacheUntil Unix timestamp
     * @return bool Succeeded?
     */
    public function setMetadataCaching($validUntil, $cacheUntil)
    {
        $currentEntity = $this->getEntity();
        $query = 'UPDATE '. self::$prefix .'entity
            SET metadata_valid_until = ?, metadata_cache_until = ?
            WHERE `eid` = ? AND `revisionid` = ?;';
        $params = array(
            date('Y-m-d H:i:s', $validUntil),
            date('Y-m-d H:i:s', $cacheUntil),
            $currentEntity->getEid(),
            $currentEntity->getRevisionid()
        );
        return (bool)$this->execute($query, $params);
    }
}