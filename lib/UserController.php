<?php
/**
 * Controller for users
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

use Symfony\Component\Security\Core\SecurityContext;
use \Symfony\Component\Security\Core\SecurityContextInterface;
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Service\ConnectionService;

/**
 * Controller for users
 *
 * Basic functionality for handling user like added users to entities.
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
class sspmod_janus_UserController extends sspmod_janus_Database
{
    /**
     * Configuration
     *
     * @var ConfigProxy
     */
    private $_config;

    /**
     * User
     *
     * @var sspmod_janus_User
     */
    private $_user;

    /**
     * List of user connected entities
     * @var sspmod_janus_Entity[]
     */
    private $_entities;

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @var ConnectionService
     */
    private $connectionService;

    /**
     * @param ConfigProxy $config
     * @param SecurityContextInterface $securityContext
     * @param ConnectionService $connectionService
     */
    public function __construct(ConfigProxy $config, SecurityContextInterface $securityContext, ConnectionService $connectionService)
    {
        $this->_config = $config;
        $this->securityContext = $securityContext;
        $this->connectionService = $connectionService;
    }

    /**
     * Set the user for the user to be handled
     *
     * @param string|sspmod_janus_User $user The user email or a user object
     *
     * @return sspmod_janus_User|bool Return the user or false if the user can
     * not be loaded
     * @throws InvalidArgumentException If parsed argument is neither a valid
     * email address og a user object
     * @since Method available since Release 1.0.0
     */
    public function setUser($user)
    {
        // If $user is an email address
        if (is_string($user)) {
            $this->_user = new sspmod_janus_User();
            $this->_user->setUserid($user);
            if (!$this->_user->load(sspmod_janus_User::USERID_LOAD)) {
                return false;
            }
            // If $user is a sspmod_janus_User object
        } else if (is_a($user, 'sspmod_janus_User')) {
            $this->_user = $user;
        } else {
            throw new InvalidArgumentException(
                'Argument must be an email address or instance of sspmod_janus_User.'
            );
        }
        return $this->_user;
    }

    /**
     * Load entities that user has access to
     *
     * @param null|string Workflow state the entity has to be in
     * @param null|string Workflow state the entity must NOT be in
     * @param null|string Field to sort on
     * @param null|string Direction to sort in ('ASC'|'DESC')
     * @return bool True on success and false on error.
     * @since Method available since Release 1.0.0
     * @throws Exception if loading fails
     */
    private function _loadEntities($state = null, $state_exclude = null, $sort = null, $order = null)
    {
        if(!$this->securityContext->isGranted('allentities')) {
            $allowedUserId = $this->_user->getUid();
        } else {
            $allowedUserId = null;
        }

        $filter = array(
            'state' => $state,
            'stateExclude' => $state_exclude,
            'allowedUserId' => $allowedUserId,
        );
        $connectionCollection = $this->connectionService->findDescriptorsForFilters(
            $filter,
            $sort,
            $order
        );

        $this->_entities = array();
        /** @var $connectionDto \Janus\ServiceRegistry\Connection\ConnectionDto */
        foreach ($connectionCollection->connections AS $connectionDto) {
            $entity = new sspmod_janus_Entity($this->_config);
            $entity->setEid($connectionDto->id);
            $entity->setRevisionid($connectionDto->revisionNr);
            if(!is_null($state)) {
                $entity->setWorkflow($state);
            }
            if ($entity->load()) {
                $this->_entities[] = $entity;
            } else {
                \SimpleSAML\Logger::error(
                    'JANUS:UserController:_loadEntities - Entity could not be
                    loaded: ' . var_export($entity, true)
                );
            }
        }
        return true;
    }

    /**
     * Return the entities that the user has access to
     *
     * @param bool $force Force the method to reload the list of entities
     * @param null $state
     * @param null $state_exclude
     * @param null $sort
     * @param null $order
     * @return bool|sspmod_janus_Entity[]
     */
    public function getEntities($force = false, $state = null, $state_exclude = null, $sort = null, $order = null)
    {
        assert('is_bool($force);');

        if (empty($this->_entities) || $force) {
            if (!$this->_loadEntities($state, $state_exclude, $sort, $order)) {
                return false;
            }
        }
        return $this->_entities;
    }

    /**
     * Checks if identity id is in use
     *
     * @param   string $entityid
     * @param   string message by reference
     * @return  boolean true if in use
     */
    public function isEntityIdInUse($entityid, &$message)
    {
        // Check if the entity id is already used on letest revision
        $st = $this->execute(
            'SELECT count(*) AS count
            FROM '. $this->getTablePrefix() .'connection je
            WHERE `name` = ?',
            array($entityid)
        );

        // @todo It would be better to let db class throw an exception
        if ($st === false) {
            $message = 'error_db';
            return true;
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['count'] > 0) {
            $message = 'error_entity_exists';
            return true;
        }

        return false;
    }

    /**
     * Create new entity with parsed entityid
     *
     * Create a new entity and give the user access to the entity.
     *
     * @param string $entityid Entity id for the new entity
     * @param string $type     Entity type
     * @param string $metadataUrl The -optional- metadata url for the new entity
     *
     * @return sspmod_janus_Entity|bool Returns the entity or false on error.
     * @since Method available since Release 1.0.0
     */
    public function createNewEntity($entityid, $type, $metadataUrl = null)
    {
        assert('is_string($entityid)');
        assert('is_string($type)');

        if($this->isEntityIdInUse($entityid, $errorMessage)) {
            return $errorMessage;
        }

        $startstate = $this->_config->getString('workflowstate.default');

        // Instantiate a new entity
        $entity = new sspmod_janus_Entity($this->_config, true);
        $entity->setEntityid($entityid);
        $entity->setWorkflow($startstate);
        $entity->setType($type);
        $entity->setUser($this->_user->getUid());
        $entity->setRevisionnote('Entity created.');
        if ($metadataUrl) {
            $entity->setMetadataURL($metadataUrl);
        }
        $entity->save(array());

        $adminUtil = new sspmod_janus_AdminUtil();
        $adminUtil->addUserToEntity($entity->getEid(), $this->_user->getUid());

        $ec = sspmod_janus_DiContainer::getInstance()->getEntityController();
        $ec->setEntity($entity);

        $update = false;
        
        // Get metadatafields for new type
        $nm_mb = new sspmod_janus_MetadataFieldBuilder(
            $this->_config->getArray('metadatafields.' . $type)
        );
        $metadatafields = $nm_mb->getMetadataFields();
        
        // Add all required fileds
        foreach ($metadatafields AS $mf) {
            if (isset($mf->required) && $mf->required === true) {
                $ec->addMetadata($mf->name, $mf->default);
                $update = true;
            }
        }
        
        if ($update === true) {
            $ec->saveEntity();
        }

        // Reset list of entities
        $this->_entities = null;
        $this->_loadEntities();

        return $entity->getEid();
    }

    /**
     * Return the user
     *
     * @return sspmod_janus_Use
     * @see    sspmod_janus_User
     * @since  Method available since Release 1.0.0
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Retrive all users in the system
     *
     * The method will retrive all users in the system. NOTE this method will be
     * moved/rewritten in the future.
     *
     * @return array All users in the system
     */
    public function getUsers()
    {
        $st = $this->execute('SELECT * FROM '. $this->getTablePrefix() .'user ORDER BY `userid`;');

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $users = array();
        foreach($rs AS $row) {
            $user = new sspmod_janus_User();
            $user->setUid($row['uid']);
            $user->load();
            $users[] = $user;
        }
        
        return $users;
    }

    public function searchEntities($query, $state = null, $state_exclude = null, $sort = null, $order = null)
    {
        if (empty($this->_entities)) {
            if (!$this->_loadEntities($state, $state_exclude, $sort, $order)) {
                return false;
            }
        }
        //$this->_entities are sspmod_janus_Entity instances
        foreach($this->_entities AS $key => $entity) {
            $nameNoMatch = stripos($entity->getPrettyname(), $query) === false && stripos($entity->getEntityId(), $query) === false;
            if ($nameNoMatch && !$this->_metadataContainsValue($entity->getEid(), $entity->getRevisionid(), $query)) {
                unset($this->_entities[$key]);
            }
        }
       return $this->_entities;
    }

    /**
     * Loads deployable workflow states from config
     *
     * @return array $deployableStateList
     */
    private function _loadDeployableWorkflowStates()
    {
        static $deployableStateList = array();

        if(empty($deployableStateList)) {
            $stateList = $this->_config->getValue('workflowstates');
            foreach($stateList as $stateName => $stateConfig) {
                $isDeployable = array_key_exists('isDeployable', $stateConfig)
                    && true === $stateConfig['isDeployable'];
                if($isDeployable) {
                    $deployableStateList[] = $stateName;
                }
            }

            // Backwards compatibility, if no states are marked as deployable, all states are used
            $noStatesMarkedAsDeployable = empty($deployableStateList);
            if($noStatesMarkedAsDeployable) {
                $deployableStateList = array_keys($stateList);
            }
        }

        return $deployableStateList;
    }

    /**
     * Retrieve all Eids for entities of a certain type.
     *
     * @param String $type The type of entity, e.g. "saml20-idp"
     * @return array all entities that have been found
     */
    public function searchEntitiesByType($type)
    {
        $deployableWorkflowStateList = $this->_loadDeployableWorkflowStates();

        $query = "
            SELECT      CONNECTION_REVISION.`eid`
                        ,CONNECTION_REVISION.`revisionid`
                        ,CONNECTION_REVISION.`entityid`
                        ,CONNECTION_REVISION.`state`
            FROM        " . $this->getTablePrefix() . "connection AS CONNECTION
            INNER JOIN  " . $this->getTablePrefix() . "connectionRevision AS CONNECTION_REVISION
                ON CONNECTION_REVISION.eid = CONNECTION.id
                AND CONNECTION_REVISION.revisionid = CONNECTION.revisionNr
            WHERE       CONNECTION.`type` = ?
        ";
        $queryVariables = array($type);

        // Add deployabe state check
        $nrOfWorkflowStates = count($deployableWorkflowStateList);
        $fWorkflowStateInPlaceholders = substr(str_repeat('?,',$nrOfWorkflowStates), 0, -1);
        $query .= " AND CONNECTION_REVISION.`state` IN(" . $fWorkflowStateInPlaceholders . ")";
        $queryVariables = array_merge($queryVariables, $deployableWorkflowStateList);

        $st = $this->execute($query, $queryVariables);

        if ($st === false) {
            return 'error_db';
        }

        $this->_entities = array();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows AS $row) {
            $entity = new sspmod_janus_Entity($this->_config);
            $entity->setEid($row['eid']);
            $entity->setRevisionid($row['revisionid']);
            $entity->setWorkflow($row['state']);
            if ($entity->load()) {
                $this->_entities[] = $entity;
            } else {
                \SimpleSAML\Logger::error(
                    'JANUS:UserController:searchEntitiesByType - Entity could not be
                    loaded, eid: '.$row['eid']
                );
            }
        }
        return $this->_entities;        
    }

    /**
     * Retrieve all Eids for entities that match a certain metadata value.
     * 
     * The query is revision aware (only searches the latest revision of every
     * entity)
     * 
     * Note that this function supports regular expressions in the metadata 
     * value. If a metadata entry in the database is a regular expression, 
     * it will be matched against the $value passed to this function. This
     * works only one way, it's not possible to pass a regular expression 
     * to this function; the regex must be in the db.
     * 
     * @param String $key   The metadata key on which to perform the search
     * @param String $value The value to search for. 
     */
    public function searchEntitiesByMetadata($key, $value)
    {
        assert('is_string($key)');
        assert('is_string($value)');

        $st = $this->execute("
            SELECT  DISTINCT CONNECTION_REVISION.eid
            FROM        " . $this->getTablePrefix() . "metadata AS METADATA
            INNER JOIN  " . $this->getTablePrefix() . "connectionRevision AS CONNECTION_REVISION
                ON  CONNECTION_REVISION.id = METADATA.connectionRevisionId
            INNER JOIN  " . $this->getTablePrefix() . "connection AS CONNECTION
                ON  CONNECTION.id = CONNECTION_REVISION.eid
                AND CONNECTION.revisionNr = CONNECTION_REVISION.revisionid
            WHERE   METADATA.`key` = ?
                AND (
                    (METADATA.value=?)
                    OR (? REGEXP CONCAT('^',METADATA.value,'\$'))
                )
                ",
                array($key, $value, $value)
            );

        if ($st === false) {
            return 'error_db';
        }

        $this->_entities = array();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows AS $row) {
            $entity = new sspmod_janus_Entity($this->_config);
            $entity->setEid($row['eid']);
            if ($entity->load()) {
                $this->_entities[] = $entity;
            } else {
                \SimpleSAML\Logger::error(
                    'JANUS:UserController:searchEntitiesByMetadata - Entity could not be
                    loaded, eid: '.$row['eid']
                );
            }
        }
        return $this->_entities;
    }

    /**
     * Return if there are metadata entries where the value contains the query string
     * for a given entity eid/revision
     *
     * @param String $eid   The eid of the metadata
     * @param String $revisionId   The revisionId of the metadata
     * @param String $query   The query string for matching the value
     * @todo integrate this in the load method
     */
    private function _metadataContainsValue($eid, $revisionId, $query)
    {
        $st = $this->execute(
            'SELECT COUNT(*) as COUNT_MD
            FROM '. $this->getTablePrefix() .'connectionRevision AS CR
            INNER JOIN '. $this->getTablePrefix() .'metadata AS MD
                ON MD.connectionRevisionId = CR.id
                AND MD.`value` LIKE ?
            WHERE CR.`eid` = ?
            AND CR.`revisionid` = ?;',
            array('%'.$query.'%', $eid, $revisionId)
        );

        if ($st === false) {
            return false;
        }

        return $st->fetchColumn() > 0;
    }
}
?>
