<?php


use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataTreeBuilder;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;
use Janus\ServiceRegistry\Command\FindConnectionRevisionCommand;
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

/**
 * An entity
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.0.0
 */
/**
 * An entity
 *
 * Contains basic functionality used for entity generation and management.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_Entity extends sspmod_janus_Database
{
    /** @var ConfigProxy */
    private $_config;

    /**
     * Autoincrementing id unique for each combination of eid/revisionid
     * @var int
     */
    private $_id;

    /**
     * @var Revision
     */
    private $currentRevision;

    /**
     * Internal id for referencing the entity
     * @var int
     */
    private $_eid;

    /**
     * Pointer to revision id of parent entity
     * @var int
     */
    private $_parent;

    /**
     * Revision note for entity
     * @var int
     */
    private $_revisionnote;

    /**
     * Notes for entity
     * @var int
     */
    private $_notes;

    /**
     * Entity id
     * @var string
     */
    private $_entityid;

    /**
     * Revision id.
     * @var int Revision number.
     */
    private $_revisionid;

    /**
     * Current workflow
     * @var string
     */
    private $_workflow;

    /**
     * Entity type
     * @var string
     */
    private $_type;

    /**
     * Expiration date of current entity.
     * @var DateTime
     */
    private $_expiration;

    /**
     * URL of the entities metadata
     * @var string
     */
    private $_metadataurl;

    /**
     * Entity allowes all other entities
     * @var string Will possible change in the future
     */
    private $_allowedall = 'yes';

    /**
     * Indicates whether that entity data has been modified
     * @var bool
     */
    private $_modified = false;

    private $_arpAttributes;

    private $_manipulation;
    
    private $_prettyname;
    
    private $_user;
    private $_created;
    private $_active = 'yes';

    /**
     * Create new entity
     *
     * @param array $config Configuration for the database
     */
    public function __construct($config)
    {
        $this->_config = $config;

    }

    public function loadFromDto(ConnectionDto $connectionDto)
    {
        $this->_eid             = $connectionDto->id;
        $this->_entityid        = $connectionDto->name;
        $this->_revisionid      = $connectionDto->revisionNr;
        $this->_workflow        = $connectionDto->state;
        $this->_type            = $connectionDto->type;
        $this->_expiration      = $connectionDto->expirationDate ? $connectionDto->expirationDate->format('Y-m-d H:i:s') : '';
        $this->_metadataurl     = $connectionDto->metadataUrl;
        $this->_allowedall      = $connectionDto->allowAllEntities;
        $this->_parent          = $connectionDto->parentRevisionNr;
        $this->_revisionnote    = $connectionDto->revisionNote;
        $this->_arpAttributes   = $connectionDto->arpAttributes;
        $this->_user            = $connectionDto->updatedByUserName;
        $this->_created         = $connectionDto->createdAtDate ? $connectionDto->createdAtDate->format('Y-m-d H:i:s') : '';
        $this->_active          = $connectionDto->isActive ? 'yes' : 'no';
        $this->_manipulation    = $connectionDto->manipulationCode;
        $this->_notes           = $connectionDto->notes;
        $this->_modified        = false;
        return $this;
    }

    /**
     * Save entity data
     *
     * @param array $metadataCollection
     * @throws Exception
     */
    public function save(
        array $metadataCollection
    )
    {
        if (empty($this->_entityid) && empty($this->_eid)) {
            throw new Exception("Cannot save connection since neither an entityid nor an eid was set");
        }

        $dto = new ConnectionDto();
        $dto->id = $this->_eid;
        $dto->name = $this->_entityid;
        $dto->type = $this->_type;
        $dto->parentRevisionNr = $this->_revisionid;
        $dto->revisionNote = $this->_revisionnote;
        $dto->state = $this->_workflow;
        // Convert expiration date to datetime object
        $expirationDate = $this->_expiration;
        if (!is_null($expirationDate)) {
            $expirationDate = \DateTime::createFromFormat(DateTime::ATOM, $this->_expiration);
        }
        $dto->expirationDate = $expirationDate;
        $dto->metadataUrl = $this->_metadataurl;
        $dto->allowAllEntities =  $this->_allowedall == 'yes';
        $dto->arpAttributes = $this->_arpAttributes;
        $dto->manipulationCode = $this->_manipulation;
        $dto->isActive =  $this->_active == 'yes';
        $dto->notes = $this->_notes;

        // Build nested metadata collection
        $flatMetadataCollection = array();
        /** @var $metadata Metadata */
        foreach ($metadataCollection as $metadata) {
            $flatMetadataCollection[$metadata->getKey()] =  $metadata->getValue();
        }
        $metadataAssembler = new MetadataTreeBuilder();
        $nestedMetadataCollection = $metadataAssembler->build($flatMetadataCollection, new MetadataDefinitionHelper($this->_config), $this->_type);
        $dto->metadata =$nestedMetadataCollection;

        $connection = $this->getConnectionService()->save($dto, true);

        $this->_eid = $connection->getId();
        $this->currentRevision = $connection->getLatestRevision();
        $this->_id = $this->currentRevision->getId();
        $this->_revisionid = $this->currentRevision->getRevisionNr();

        $this->_modified = false;
    }

    /**
     * Get the newest revision id of entity
     *
     * @param   string  [$state] Optional state
     * @return  string  $this->_revisionid
     * @throws  Exception in case loading revision fails
     */
    private function _newestRevision($state = null)
    {
        if (!is_numeric($this->_eid)) {
            throw new Exception("Connection id not set");
        }

        $command = new FindConnectionRevisionCommand();
        $command->id = $this->_eid;
        $command->state = $state;
        $newestRevision = $this->getConnectionService()->findLatestRevisionNr($command);

        if (!is_null($newestRevision)) {
            $this->_revisionid = $newestRevision;
            return $newestRevision;
        }

        throw new Exception(
            'JANUS:Entity:load - Could not get newest revision.'
        );
    }

    /**
     * Get the eid
     *
     * If the entityID is supplied, the eid will be found unless multiple eid's 
     * is returned for the same entityID
     *
     * @return bool|string true if eid is found else false or an error code or an Exception...
     * @throws Exception
     */
    private function _findEid()
    {
        if (!isset($this->_entityid)) {
            return false;
        }

        $st = $this->execute(
            'SELECT DISTINCT(`id`) AS eid
            FROM `'. $this->getTablePrefix() .'connection`
            WHERE `name` = ?;',
            array($this->_entityid)
        );

        if ($st === false) {
            return 'error_db';
        }

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) == 0) {
            throw new Exception("Entity '{$this->_entityid}' does not exist");
        }
        if (count($rows) > 1) {
            return 'error_entityid_not_unique';
        }

        $this->_eid = $rows[0]['eid'];
        return true;
    }

    /**
     * Retrieve entity data from database
     *
     * Loads the entity data from the database. If either _eid and _revisionid
     * is not set or an error occurs and the method returns false. If only
     * _eid is set, the newest revision will be fetched.
     *
     * @return bool
     */
    public function load()
    {
        if (empty($this->_eid) && isset($this->_entityid)) {
            $this->_findEid();
        }

        if (is_null($this->_revisionid)) {
            if(empty($this->_workflow)) {
                $this->_newestRevision();
            } else {
                $this->_newestRevision($this->_workflow);
            }
        }

        if (empty($this->_eid) || is_null($this->_revisionid)) {
            \SimpleSAML\Logger::error(
                'JANUS:Entity:load - entityid and revisionid needs to be set.'
            );
            return false;
        }

        $row = $this->_loadFromCache($this->_eid, $this->_revisionid);
        if (!$row) {
            return false;
        }

        $this->_id              = $row['id'];
        $this->_eid             = $row['eid'];
        $this->_entityid        = $row['entityid'];
        $this->_revisionid      = $row['revisionid'];
        $this->_workflow        = $row['state'];
        $this->_type            = $row['type'];
        $this->_expiration      = $row['expiration'];
        $this->_metadataurl     = $row['metadataurl'];
        $this->_allowedall      = $row['allowedall'];
        $this->_parent          = $row['parent'];
        $this->_revisionnote    = $row['revisionnote'];
        $this->_arpAttributes   = unserialize($row['arp_attributes']);
        $this->_user            = $row['user'];
        $this->_created         = $row['created'];
        $this->_active          = $row['active'];
        $this->_manipulation    = $row['manipulation'];
        $this->_notes           = $row['notes'];
        $this->_modified        = false;
        return true;
    }

    /**
     * @param int $eid
     * @param int $revisionid
     * @return bool|array
     */
    private function _loadFromCache($eid, $revisionid)
    {
        /** @var \Doctrine\Common\Cache\MemcacheCache $cacheProvider */
        $cacheProvider = sspmod_janus_DiContainer::getInstance()->getCacheProvider();

        // Try to get result from cache
        $cacheKey = 'connectionRevision-' . $eid . '-' . $revisionid;
        $cachedResult = $cacheProvider->fetch($cacheKey);

        if ($cachedResult !== false ) {
            return $cachedResult;
        }

        $row = $this->_loadFromDatabase($eid, $revisionid);
        if (!$row) {
            return null;
        }

        // Store entity in cache, note that this does not have to be flushed since a new revision
        // will trigger a new version of the cache anyway
        $cacheProvider->save($cacheKey, $row);

        return $row;
    }

    /**
     * @param int $eid
     * @param int $revisionid
     * @return bool|array
     */
    private function _loadFromDatabase($eid, $revisionid)
    {
        $st = $this->execute(
            'SELECT *
                FROM '. $this->getTablePrefix() .'connectionRevision
                WHERE `eid` = ? AND `revisionid` = ?;',
            array($eid, $revisionid)
        );

        if ($st === false) {
            return false;
        }

        return $st->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Set the eid of the entity
     *
     * A valid eid is required to save and load the entity. Set _modified to
     * true.
     *
     * @param string $eid Entity identifier
     *
     * @return void
     *
     * @since Method available since Release [FIRST RELEASE FUNCTION APPEARED IN]
     */
    public function setEid($eid)
    {
        assert('is_integer($eid) || ctype_digit($eid)');

        $this->_eid = $eid;

        $this->_modified = true;
    }

    /**
     * Set entity id of entity
     *
     * Method for setting the entity id. Method sets _modified to true.
     *
     * @param string $entityid Entity id
     *
     * @return bool
     *
     * @since Method available since Release 1.0.0
     */
    public function setEntityid($entityid)
    {
        assert('is_string($entityid)');

        if ($this->_entityid != $entityid) {
            $this->_entityid = $entityid;
            $this->_modified = true;
            return true;
        } 
        return false;
    }

    /**
     * Set notes of entity
     *
     * Method for setting the notes. Method sets _modified to true.
     *
     * @param string $notes
     *
     * @return void
     *
     */
    public function setNotes($notes)
    {
        assert('is_string($notes)');

        if ($this->_notes != $notes) {
            $this->_notes = $notes;
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Set revision id.
     *
     * Method for setting the revision id. The revision id is automaticlly
     * increased by one when the entity is saved. Method sets _modified to
     * true.
     *
     * @param int $revisionid Revision id of entity.
     *
     * @return void
     */
    public function setRevisionid($revisionid)
    {
        $this->_revisionid = $revisionid;

        $this->_modified = true;
    }

    /**
     * Set the entity type
     *
     * The method will only return true if the new value is different from the
     * already set value.
     *
     * @param string $type Entity type
     *
     * @return bool True on change, false othervise
     * @todo Check that the type is valid
     * @todo Should return true on success, not only on change
     */
    public function setType($type)
    {
        assert('is_string($type)');

        if ($type != $this->_type) {
            $this->_type = $type;
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Retrive the entity revision id
     *
     * @return int Revision id
     */
    public function getRevisionid()
    {
        return $this->_revisionid;
    }

    /**
     * @return Revision
     */
    public function getCurrentRevision()
    {
        return $this->currentRevision;
    }

    /**
      * Retrive the unique entity revision identifier
      *
      * @return int The entity identifier
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Retrive the entity identifier
     *
     * @return int The entity identifier
     * @since Method available since Release ??
     */
    public function getEid()
    {
        return $this->_eid;
    }

    /**
     * Entity entity id
     *
     * @return string Entity id
     * @isnce Method available since Release 1.0.0
     */
    public function getEntityid()
    {
        return $this->_entityid;
    }

    /**
     * Retrive entity type
     *
     * @return string Entity type
     * @since Method available since Release 1.0.0
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the allowedAll flag for the entity
     *
     * The function will only return true if the flag is changed.
     *
     * @param string $allowedall String 'yes'/'no'
     *
     * @return bool True on change, false othervise
     * @since      Method available since Release 1.0.0
     * @todo Should return true on success. Also if the new value is the same as
     * the old one.
     */
    public function setAllowedAll($allowedall)
    {
        assert('is_string($allowedall)');

        if ($allowedall != $this->_allowedall) {
            $this->_allowedall = $allowedall;
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Retrieve the allowall flag for the entity
     * @return string allowAll flag
     * @since Method available since Release 1.0.0
     */
    public function getAllowedAll()
    {
        return $this->_allowedall;
    }

    /**
     * Set entity workflow
     *
     * @param string $workflow Entity workflow
     *
     * @return bool True if workflow was changed
     * @since Method available since Release 1.0.0
     */
    public function setWorkflow($workflow)
    {
        assert('is_string($workflow)');

        if ($workflow != $this->_workflow) {
            $this->_workflow = $workflow;
            $this->_modified = true;
            return true;
        }
        return false;
    }

    /**
     * Get entity workflow
     *
     * @return string Entity workflow
     * @since Method available since Release 1.0.0
     */
    public function getWorkflow()
    {
        return $this->_workflow;
    }

    /**
     * Set entity parent entity
     *
     * @param int $parent Parent entity eid
     *
     * @return void
     * @since Method available since Release 1.0.0
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Get entity parent entity
     *
     * @return int
     * @since Method available since Release 1.0.0
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Set entity revision note
     *
     * @param string $revisionnote The revision note
     *
     * @return void
     * @since Method available since Release 1.0.0
     */
    public function setRevisionnote($revisionnote)
    {
        assert('is_string($revisionnote)');

        $this->_revisionnote = $revisionnote;
    }

    /**
     * Get entity revision note
     *
     * @return string The revision note
     * @since Method available since Release 1.0.0
     */
    public function getRevisionnote()
    {
        return $this->_revisionnote;
    }

    /**
     * Get the metadata URL
     *
     * @return string The matadata URL
     * @since Method available since Release 1.4.0
     */
    public function getMetadataURL()
    {
        return $this->_metadataurl;
    }

    /**
     * Get the notes
     *
     * @return string The notes
     */
    public function getNotes()
    {
        return $this->_notes;
    }
    /**
     * Set the metadata URL
     *
     * @param string $url The metadata URL
     *
     * @return void
     * @since Method available since Release 1.4.0
     */
    public function setMetadataURL($url)
    {
        assert('is_string($url)');

        $this->_metadataurl = $url;
    }

    public function setArpAttributes($arpAttributes) {

        if ($arpAttributes === $this->_arpAttributes) {
            return false;
        }
        $this->_arpAttributes = $arpAttributes;
        $this->_modified = true;
        return true;
    }

    public function getArpAttributes() {
        return $this->_arpAttributes;
    }

    public function setManipulation($manipulationCode) {
        if ($this->_manipulation === $manipulationCode || (empty($manipulationCode) && empty($this->_manipulation))) {
            return false;
        }

        $this->_manipulation = $manipulationCode;
        $this->_modified = true;
        return true;
    }

    public function getManipulation() {
        return $this->_manipulation;
    }
    
    public function getPrettyname() {
        if (isset($this->_prettyname)) {
            return $this->_prettyname;
        }

        /** @var string $fieldName */
        $fieldName = $this->_config->getString('entity.prettyname', NULL);

        $mb = new sspmod_janus_MetadataFieldBuilder(
            $this->_config->getArray('metadatafields.' . $this->_type)
        );
        $metadataFields = $mb->getMetadataFields();

        if (!is_null($fieldName)) {
            $rows = $this->loadPrettyNameFromCache($fieldName);

            if(empty($rows)) {
                $this->_prettyname =  $this->_entityid;
            } else if(isset($metadataFields[$fieldName]->default) && $metadataFields[$fieldName]->default == $rows[0]['value']) {
                $this->_prettyname =  $this->_entityid; 
            } else {
                $this->_prettyname = $rows[0]['value'];
            }
        } else {
            $this->_prettyname =  $this->_entityid;
        }

        return $this->_prettyname;
    }

    /**
     * Tries to load Pretty name from cache.
     *
     * @param string $fieldname
     * @return array|bool
     */
    private function loadPrettyNameFromCache($fieldname)
    {
        $cacheProvider = sspmod_janus_DiContainer::getInstance()->getCacheProvider();

        $id = $this->_id;
        $eid = $this->_eid;
        $revisionId = $this->_revisionid;

        // Try to get result from cache
        $cacheKey = 'entity-prettyname' . $eid . '-' . $revisionId;
        $cachedResult = $cacheProvider->fetch($cacheKey);

        if ($cachedResult !== false) {
            return $cachedResult;
        }

        $rows = $this->_loadPrettyNameFromDatabase($id, $fieldname);
        if (!is_array($rows)) {
            return false;
        }

        // Store entity pretty nane in cache, note that this does not have to be flushed since a new revision
        // will trigger a new version of the cache anyway
        $cacheProvider->save($cacheKey, $rows);

        return $rows;
    }

    /**
     * @param int $eid
     * @param string $fieldName
     * @return array|bool
     */
    private function _loadPrettyNameFromDatabase($id, $fieldName)
    {
        $st = $this->execute('
                SELECT t1.value AS value
                FROM '. $this->getTablePrefix() .'metadata AS t1
                WHERE t1.connectionRevisionId = ? AND t1.key = ?;',
            array($id, $fieldName)
        );

        if ($st === false) {
            return false;
        }

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser() {
        return $this->_user;
    }

    public function getCreated() {
        return $this->_created;
    }

    public function setUser($user) {
        $this->_user = $user;
    } 

    public function getActive() {
        return $this->_active;
    }

    public function setActive($active) {
        $this->_active = $active;
    }
}
