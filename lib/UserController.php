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
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
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
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      Class available since Release 1.0.0
 */
class sspmod_janus_UserController extends sspmod_janus_Database
{
    /**
     * Configuration
     *
     * @var SimpleSAML_Configuration
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
     * @var array List of sspmod_janus_Entity
     */
    private $_entities;

    /**
     * Create a new user controller
     *
     * @param SimpleSAML_Configuration $config JANUS configuration
     *
     * @since Method available since Release 1.0.0
     */
    public function __construct(SimpleSAML_Configuration $config)
    {
        // Send DB config to parent class
        parent::__construct($config->getValue('store'));
        $this->_config = $config;
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
            $this->_user = new sspmod_janus_User($this->_config->getValue('store'));
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
     * @return bool True on success and false on error.
     * @since Method available since Release 1.0.0
     */
    private function _loadEntities($state = null, $state_exclude = null)
    {
        $excludeSQL = ';';
        if (!is_null($state_exclude)) {
            $excludeSQL = ' AND `eid` NOT IN (SELECT DISTINCT `eid` FROM janus__entity WHERE `state` IN (\'' . $state_exclude . '\'));';
        }

        $guard = new sspmod_janus_UIguard($this->_config->getArray('access', array()));

        if($guard->hasPermission('allentities', null, $this->_user->getType(), TRUE)) {
            if(!is_null($state)) {
                $st = $this->execute('SELECT DISTINCT `eid` FROM '. self::$prefix .'entity WHERE `state` = ?' . $excludeSQL,
                    array($state)                     
                );
            } else {
                $st = $this->execute('SELECT DISTINCT `eid` FROM '. self::$prefix .'entity' . $excludeSQL);
            }

            if ($st === false) {
                return false;
            }

        } else {
            if(!is_null($state)) {
                $st = $this->execute(
                    'SELECT * 
                    FROM '. self::$prefix .'hasEntity t1, '. self::$prefix .'entity t2 
                    WHERE t1.`uid` = ? ANd t1.eid = t2.eid AND t2.state = ?' . $excludeSQL,
                    array($this->_user->getUid(), $state)
                );
            } else {
                $st = $this->execute(
                    'SELECT * 
                    FROM '. self::$prefix .'hasEntity 
                    WHERE `uid` = ?' . $excludeSQL,
                    array($this->_user->getUid())
                );
            }

            if ($st === false) {
                return false;
            }
        }

        $this->_entities = array();
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rs AS $row) {
            $entity = new sspmod_janus_Entity($this->_config);
            $entity->setEid($row['eid']);
            if(!is_null($state)) {
                $entity->setWorkflow($state);
            }
            if ($entity->load()) {
                $this->_entities[] = $entity;
            } else {
                SimpleSAML_Logger::error(
                    'JANUS:UserController:_loadEntities - Entity could not be
                    loaded, entity id: '.$row['entityid']
                );
            }
        }
        return true;
    }

    /**
     * Return the entities that the user has access to
     *
     * @param bool $force Force the method to reload the list of entities
     *
     * @return bool|array Array of sspmod_janus_Entity or false on error
     * @since Method available since Release 1.0.0
     */
    public function getEntities($force = false, $state = null, $state_exclude = null)
    {
        assert('is_bool($force);');

        if (empty($this->_entities) || $force) {
            if (!$this->_loadEntities($state, $state_exclude)) {
                return false;
            }
        }
        return $this->_entities;
    }

    /**
     * Create new entity with parsed entityid
     *
     * Create a new entity and give the user access to the entity.
     *
     * @param string $entityid Entity id for the new entity
     * @param string $type     Entity type
     *
     * @return sspmod_janus_Entity|bool Returns the entity or false on error.
     * @since Method available since Release 1.0.0
     */
    public function createNewEntity($entityid, $type)
    {
        assert('is_string($entityid)');
        assert('is_string($type)');

        // Check if the entity id is already used
        $st = $this->execute(
            'SELECT count(*) AS count 
            FROM '. self::$prefix .'entity 
            WHERE `entityid` = ?;',
            array($entityid)
        );

        if ($st === false) {
            return 'error_db';
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($row[0]['count'] > 0) {
            return 'error_entity_exists';
        }

        $startstate = $this->_config->getString('workflowstate.default');

        // Instanciate new entity
        $entity = new sspmod_janus_Entity($this->_config, true);
        $entity->setEntityid($entityid);
        $entity->setWorkflow($startstate);
        $entity->setType($type);
        $entity->setArp('0');
        $entity->setUser($this->_user->getUid());
        $entity->setRevisionnote('Entity created.');
        $entity->save();

        $st = $this->execute(
            'INSERT INTO '. self::$prefix .'hasEntity 
            (`uid`, `eid`, `created`, `ip`) 
            VALUES 
            (?, ?, ?, ?);',
            array(
                $this->_user->getUid(),
                $entity->getEid(),
                date('c'),
                $_SERVER['REMOTE_ADDR'],
            )
        );

        if ($st === false) {
            return 'error_db';
        }

        $ec = new sspmod_janus_EntityController($this->_config);
        $ec->setEntity($entity);

        $update = false;
        foreach ($this->_config->getValue('metadatafields.'.$type) AS $mk => $mv) {
            if (isset($mv['required']) && $mv['required'] === true) {
                $ec->addMetadata($mk, $mv['default']);
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
        $st = $this->execute('SELECT * FROM '. self::$prefix .'user;');

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        $users = array();
        foreach($rs AS $row) {
            $user = new sspmod_janus_User($this->_config->getValue('store'));
            $user->setUid($row['uid']);
            $user->load();
            $users[] = $user;
        }
        
        return $users;
    }

    public function searchEntities($query, $state = null)
    {
        if (empty($this->_entities)) {
            if (!$this->_loadEntities($state)) {
                return false;
            }
        }

        foreach($this->_entities AS $key => $entity) {
            if (stripos($entity->getPrettyname(), $query) === false && stripos($entity->getEntityId(), $query) === false) {
                unset($this->_entities[$key]);
            } 
        }

       return $this->_entities;
    }
    
    /**
     * Retrieve all Eids for entities of a certain type.
     * 
     * @param String $type The type of entity, e.g. "saml20-idp"
     * @return array all entities that have been found
     */
    public function searchEntitiesByType($type)
    {
        $st = $this->execute(
            'SELECT DISTINCT eid 
            FROM '. self::$prefix ."entity
            WHERE `type` = ?",
            array($type)
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
                SimpleSAML_Logger::error(
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

        $st = $this->execute(
            'SELECT DISTINCT eid 
            FROM '. self::$prefix ."metadata jm
            WHERE `key` = ?
            AND ((value=?) OR (? REGEXP CONCAT('^',value,'\$')))
            AND revisionid = (SELECT MAX(revisionid) FROM ".self::$prefix."metadata WHERE eid = jm.eid);",
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
                SimpleSAML_Logger::error(
                    'JANUS:UserController:searchEntitiesByMetadata - Entity could not be
                    loaded, eid: '.$row['eid']
                );
            }
        }
        return $this->_entities;
    }
}
?>
