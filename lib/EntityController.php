<?php
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

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
 * @link       http://github.com/janus-ssp/janus/
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
 * @link       http://github.com/janus-ssp/janus/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_EntityController extends sspmod_janus_Database
{
    /**
     * JANUS configuration
     * @var ConfigProxy
     */
    private $_config;

    /**
     * JANUS entity
     * @var Sspmod_Janus_Entity
     */
    private $_entity;

    /**
     * List of entity metadata
     * @var Sspmod_Janus_Metadata[]
     */
    private $_metadata;

    private $_blocked = array();
    private $_blockedLoaded = false;

    private $_allowed = array();
    private $_allowedLoaded = false;

    private $_users;

    private $_modified = false;

    private $_disableConsent = array();

    /**
     * Class constructor.
     *
     * Constructs a EntityController object.
     *
     * @param ConfigProxy $config Global SSP configuration
     */
    public function __construct(ConfigProxy $config)
    {
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
     * @param string|sspmod_janus_Entity $entity     eid (numeric), entityid or entity instance
     * @param string                     $revisionid Revision id, for loading a
     * previous revision
     *
     * @return sspmod_janus_Entity|false|null Returns the entity or false on error
     * @throws \InvalidArgumentException
     */
    public function &setEntity($entity, $revisionid = null)
    {
        if ($entity instanceof Sspmod_Janus_Entity) {
            $this->_entity = $entity;
        } else if (is_scalar($entity)) {
            $this->_entity = $this->_createEntity($entity, $revisionid);
        } else {
            throw new \InvalidArgumentException("Entity argument must be set");
        }

        return $this->_entity;
    }

    /**
     * Creates new entity
     *
     * @param   string  $id           eid|entityid
     * @param   string  [$revisionid] Optional revision id
     * @return  sspmod_janus_Entity|boolean entity instance or false in case of error
     * @throws  Exception   in case entity could no be loaded
     */
    private function _createEntity($id, $revisionid = null)
    {
        $this->_metadata = null;
        $entity = new sspmod_janus_Entity($this->_config);

        if (ctype_digit((string) $id)) {
            $entity->setEid($id);
        } else {
            $entity->setEntityid($id);
        }

        // If a revisionid is parsed
        if (isset($revisionid)) {
            assert('ctype_digit($revisionid);');
            $entity->setRevisionid($revisionid);
        }

        // Load entity information
        if (!$entity->load()) {
            \SimpleSAML\Logger::error(
                __CLASS__ . ':setEntity - Entity could not load.'
                . ' Entityid: '. $id . ' - Rid: '. $revisionid
            );
            return false;
        }

        return $entity;
    }

    /**
     * Load metadata.
     *
     * Loades the metadata associated with the entity.
     *
     * @return bool Return true on success and false on error.
     * @throws SimpleSAML_Error_Exception
     */
    private function _loadMetadata()
    {
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $connectionRevisionId = $this->_entity->getId();
        $revisionId = $this->_entity->getRevisionid();

        $cacheProvider = sspmod_janus_DiContainer::getInstance()->getCacheProvider();

        // Try to get result from cache
        $cacheKey = 'entity-metadata-' . $connectionRevisionId;
        $cachedResult = $cacheProvider->fetch($cacheKey);
        if ($cachedResult !== false) {
            $this->_metadata = $cachedResult;
            return true;
        }

        $st = $this->execute(
            'SELECT * 
            FROM '. $this->getTablePrefix() .'metadata
            WHERE `connectionRevisionId` = ?;',
            array($connectionRevisionId)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error(
                __CLASS__ . ':_loadMetadata - Metadata could not load.'
            );
            return false;	
        }
        $this->_metadata = array();
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $mb = new sspmod_janus_MetadataFieldBuilder(
            $this->_config->getArray('metadatafields.' . $this->_entity->getType())
        );
        $definitions = $mb->getMetadataFields();

        foreach ($rs AS $row) {
            $definition = null;
            if (isset($definitions[$row['key']])) {
                $definition = $definitions[$row['key']];
            }

            $metadata = new sspmod_janus_Metadata($definition, $row['key'], $row['value']);
            $this->_metadata[] = $metadata;
        }

        // Store metadata in cache, note that this does not have to be flushed since a new revision
        // will trigger a new version of the cache anyway
        $cacheProvider->save($cacheKey, $this->_metadata);

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
        if ($value === null || $value === '') {
            return false;
        }
        assert('is_string($key);');	
        assert('$this->_entity instanceof Sspmod_Janus_Entity');

        $mb = new sspmod_janus_MetadataFieldBuilder(
            $this->_config->getArray('metadatafields.' . $this->_entity->getType())
        );
        $fieldDefinitions = $mb->getMetadataFields();
        
        // Check if metadata is allowed
        if (!array_key_exists($key, $fieldDefinitions)) {
            \SimpleSAML\Logger::info(
                __CLASS__ . ':addMetadata - Metadata key \''
                . $key .' not allowed'
            );
            return false;
        }
        $fieldDefinition = $fieldDefinitions[$key];

        if (empty($this->_metadata)) {
            if (!$this->loadEntity()) {
                return false;
            }
        }

        $st = $this->execute(
            'SELECT count(*) AS count 
            FROM '. $this->getTablePrefix() .'metadata
            WHERE `connectionRevisionId` = ? AND `key` = ?;',
            array(
                $this->_entity->getId(),
                $key,
            )
        );

        if ($st === false) {
            \SimpleSAML\Logger::error(
                __CLASS__ . ':addMetadata - Count check failed'
            );
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['count'] > 0) {
            \SimpleSAML\Logger::error(
                __CLASS__ . ':addMetadata - Metadata already exists'
            );
            return false;
        }

        if ($fieldDefinition->getType() === 'select') {
            $allowedSelectValues = $fieldDefinition->getSelectValues();
            if (!in_array($value, $allowedSelectValues)) {
                \SimpleSAML\Logger::error(
                    __CLASS__ . ':addMetadata - Value: ' . $value . ' not allowed for field ' . $key
                );
                return false;
            } 
        }

        $metadata = new sspmod_janus_Metadata($fieldDefinition, $key, $value);
        $metadata->setConnectionRevisionId($this->_entity->getId());
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
        $old_revisionid = $this->_entity->getRevisionid();
        $this->_entity->save(
            $this->_metadata
        );
        $new_revisionid = $this->_entity->getRevisionid();

        if ($old_revisionid !== $new_revisionid) {
            $this->_modified = true;
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
            FROM '. $this->getTablePrefix() .'connectionRevision
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
                \SimpleSAML\Logger::error(
                    __CLASS__ . ':getHistory - Entity could not '
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
            FROM ' . $this->getTablePrefix() . 'connectionRevision
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
     * @deprecated See sspmod_janus_Importer::importSp
     */
    public function importMetadata20SP($metadata, &$updated, $excludedMetadataKeys = array())
    {
        $importer = new sspmod_janus_Importer(
            $this->_entity->getEntityid(),
            $this,
            $this->_config,
            $excludedMetadataKeys
        );

        $result = $importer->importSp($metadata);

        $updated = $importer->hasPerformedUpdates();
        return $result;
    }

    /**
     * @deprecated See sspmod_janus_Importer::importIdp
     */
    public function importMetadata20IdP($metadata, &$updated, $excludedMetadataKeys = array())
    {
        $importer = new sspmod_janus_Importer(
            $this->_entity->getEntityid(),
            $this,
            $this->_config,
            $excludedMetadataKeys
        );

        $result = $importer->importIdp($metadata);

        $updated = $importer->hasPerformedUpdates();
        return $result;
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
     * @param string $value Metadata value
     *
     * @return bool Return TRUE on success and FALSE on error
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
                $data->updateValue($value);
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
     * @param string $remoteEid Entity id of blocked entity
     *
     * @return true Return true on success
     * @since Method available since Release 1.0.0
     */
    public function addBlockedEntity($remoteEid)
    {
        assert('is_string($remoteEid)');

        if (!array_key_exists($remoteEid, $this->_blocked)) {
            $this->_blocked[$remoteEid] = array('remoteeid' => $remoteEid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * remove an entity from the current entity blocked list.
     *
     * @param string $remoteEid The entity to be removed
     *
     * @return true Returns true on success
     * @since Method available since Release 1.0.0
     */
    public function removeBlockedEntity($remoteEid)
    {
        if (isset($this->_blocked[$remoteEid])) {
            unset($this->_blocked[$remoteEid]);
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
     * @param string $remoteEid Entity id of blocked entity
     *
     * @return true Return true on success
     * @since Method available since Release 1.8.0
     */
    public function addAllowedEntity($remoteEid)
    {
        assert('is_string($remoteEid) && ctype_digit($remoteEid)');

        if (!array_key_exists($remoteEid, $this->_allowed)) {
            $this->_allowed[$remoteEid] = array('remoteeid' => $remoteEid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * remove an entity from the current entity allowed list.
     *
     * @param string $remoteEid The entity to be removed
     *
     * @return true Returns true on success
     * @since Method available since Release 1.8.0
     */
    public function removeAllowedEntity($remoteEid)
    {
        if (isset($this->_allowed[$remoteEid])) {
            unset($this->_allowed[$remoteEid]);
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
        return $this->_loadLinkedEntities('blocked', $this->_entity->getId());
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
        return $this->_loadLinkedEntities('allowed', $this->_entity->getId());
    }

    /**
     * Get the blocked/allowed entities from the database
     *
     * @param String $type must be 'blocked' or 'allowed'
     * @param int $connectionRevisionId id of the entity/revision
     *
     * @return bool True on success and false on error
     */
    private function _loadLinkedEntities($type, $connectionRevisionId)
    {
        $cacheProvider = sspmod_janus_DiContainer::getInstance()->getCacheProvider();

        // Try to get result from fache
        $cacheKey = 'entity-' . $type . '-entities-' . $connectionRevisionId;
        $cachedResult = $cacheProvider->fetch($cacheKey);
        if ($cachedResult !== false) {
            $this->{'_'.$type} = $cachedResult;
            return true;
        }

        $st = $this->execute(
            'SELECT linkedEntity.*,
                    remoteConnection.name as remoteentityid,
                    remoteConnection.id as remoteeid,
                    remoteConnection.revisionNr as remoterevisionid
            FROM '. $this->getTablePrefix() . $type . 'Connection linkedEntity
            INNER JOIN '. $this->getTablePrefix() . 'connection AS remoteConnection
                ON remoteConnection.id = linkedEntity.remoteeid
            WHERE linkedEntity.connectionRevisionId = ?',
            array($connectionRevisionId)
        );

        if ($st === false) {
            return false;
        }

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $this->{'_'.$type} = array();

        foreach ($rows AS $row) {
            $this->{'_'.$type}[$row['remoteeid']] = $row;
        }

        // Store linked entities in cache, note that this does not have to be flushed since a new revision
        // will trigger a new version of the cache anyway
        $cacheProvider->save($cacheKey, $this->{'_'.$type});

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
     * @return false void on success and false on error
     */
    private function _saveLinkedEntities($revision, $type)
    {
        if ($this->_modified) {
            $entityManager = $this->getEntityManager();

            // Get current entity revision

            foreach ($this->{'_'.$type} AS $linked) {
                $remoteConnection = $this->getConnectionService()->findById($linked['remoteeid']);

                // Create relation
                $className = 'Janus\ServiceRegistry\Entity\Connection\Revision\\' . ucfirst($type) . 'ConnectionRelation';
                $linkedConnectionRelation = new $className(
                    $this->_entity->getCurrentRevision(),
                    $remoteConnection
                );

                $entityManager->persist($linkedConnectionRelation);
            }

            $entityManager->flush();
            return true;
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
     * Retrieve user with permission to edit the entity from database.
     *
     * @return bool True on success and false on error
     * @since      Method available since Release 1.0.0 
     */
    private function _loadUsers()
    {
        $st = $this->execute(
            'SELECT `userid` 
            FROM '. $this->getTablePrefix() .'hasConnection t1, '. $this->getTablePrefix() .'user t2
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
     * @todo move to separate class
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

        $metaArray = array();

        foreach ($this->_metadata AS $data) {
            if (strpos($data->getKey(), ':')) {
                $keys = explode(':', $data->getKey());
                $val = $data->getValue();
                $array = array();
                while (!empty($keys)) {
                    $array = array();
                    $newKey = array_pop($keys);
                    $array[$newKey] = $val;
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
            if (!is_null($this->_entity->getArpAttributes())) {
                $metaArray['attributes'] = array_keys($this->_entity->getArpAttributes());
            }
        }
        if (!isset($metaArray['name'])) {
            $metaArray['name']['en'] = $this->_entity->getEntityid();
        }

        if (isset($metaArray['certData2']) && isset($metaArray['certData'])) {
            $keys = array();

            $keys[0] = array(
                'encryption' => FALSE,
                'signing' => TRUE,
                'type' => 'X509Certificate',
                'X509Certificate' => $metaArray['certData'],
            );
            $keys[1] = array(
                'encryption' => TRUE,
                'signing' => FALSE,
                'type' => 'X509Certificate',
                'X509Certificate' => $metaArray['certData'],
            );
            $keys[2] = array(
                'encryption' => FALSE,
                'signing' => TRUE,
                'type' => 'X509Certificate',
                'X509Certificate' => $metaArray['certData2'],
            );
            unset($metaArray['certData2']);

            $metaArray['keys'] = $keys;
        }

        return $metaArray;
    }

    /**
     * Disable consent for remote entity
     *
     * @param int $remoteeid eid of remote entity
     *
     * @return bool True on success and false on error
     */
    public function addDisableConsent($remoteeid)
    {
        if (!array_key_exists($remoteeid, $this->_disableConsent)) {
            $this->_disableConsent[$remoteeid]
                = array('remoteeid' => $remoteeid);
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Enable consent for remote entity
     *
     * @param int $remoteeid eid of remote entity
     *
     * @return true Always return true
     */
    public function removeDisableConsent($remoteeid)
    {
        assert('is_string($remoteeid)');

        if (isset($this->_disableConsent[$remoteeid])) {
            unset($this->_disableConsent[$remoteeid]);
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
        $eid = $this->_entity->getEid();
        $revisionId = $this->_entity->getRevisionid();

        $cacheProvider = sspmod_janus_DiContainer::getInstance()->getCacheProvider();

        // Try to get result from cache
        $cacheKey = 'entity-disableconsent-' . $eid . '-' . $revisionId;
        $cachedResult = $cacheProvider->fetch($cacheKey);
        if ($cachedResult !== false) {
            $this->_disableConsent = $cachedResult;
            return true;
        }

        $st = $this->execute(
            'SELECT DC.*,
                    CONNECTION.name AS remoteentityid
            FROM '. $this->getTablePrefix() .'disableConsent AS DC
            INNER JOIN  '. $this->getTablePrefix() .'connection AS CONNECTION
                ON CONNECTION.id = DC.remoteeid
            WHERE DC.`connectionRevisionId` = ?;',
            array($this->_entity->getId())
        );

        if ($st === false) {
            return false;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        $this->_disableConsent = array();

        foreach ($row AS $data) {
            $this->_disableConsent[$data['remoteentityid']] = $data;
        }

        // Store disable consent in cache, note that this does not have to be flushed since a new revision
        // will trigger a new version of the cache anyway
        $cacheProvider->save($cacheKey, $this->_disableConsent);

        return true;
    }

    /**
     * Save disable consent to database
     *
     * @return bool True on success and false on error
     * @throws \Exception
     */
    private function _saveDisableConsent()
    {
        $entityManager = $this->getEntityManager();

        foreach ($this->_disableConsent AS $disable) {
            $remoteConnection = $this->getConnectionService()->findById($disable['remoteeid']);

            // Create relation
            $linkedConnectionRelation = new Janus\ServiceRegistry\Entity\Connection\Revision\DisableConsentRelation(
                $this->_entity->getCurrentRevision(),
                $remoteConnection
            );

            $entityManager->persist($linkedConnectionRelation);
        }

        $entityManager->flush();
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
     * @return array
     */
    public function getArpAttributes()
    {
        return $this->_entity->getArpAttributes();
    }

    /**
     * Set the arpAttributes for the entity
     *
     * @param String $arpAttributes The arpAttributes
     *
     * @return void
     */
    public function setArpAttributes($arpAttributes)
    {
        $this->_entity->setArpAttributes($arpAttributes);
    }

    /**
     * Create a certificate object based on the certData field.
     *
     * @throws sspmod_janus_Exception_NoCertData
     * @throws Janus_OpenSsl_Certificate_Exception_NotAValidPem
     * @return Janus_OpenSsl_Certificate
     */
    public function getCertificate()
    {
        $metadata = $this->getMetaArray();
        if (!isset($metadata['certData']) || trim($metadata['certData'])==="") {
            return false;
        }
        else {
            return sspmod_janus_CertificateFactory::create($metadata['certData']);
        }
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
            FROM '. $this->getTablePrefix() .'connectionRevision
            WHERE `eid` = ? AND `revisionid` = ?;',
            array($currentEntity->getEid(), $currentEntity->getRevisionid())
        );

        if ($st === false) {
            \SimpleSAML\Logger::error(
                __CLASS__ . ':_loadMetadata - Metadata could not load.'
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
        $query = 'UPDATE '. $this->getTablePrefix() .'connectionRevision
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
