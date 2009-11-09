<?php
/**
 * Controller for users
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
 * Controller for users
 *
 * Basic functionality for handling user like added users to entities.
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
     * @param SimpleSAML_Configuration &$config JANUS configuration
     *
     * @since Method available since Release 1.0.0
     */
    public function __construct(SimpleSAML_Configuration &$config)
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
    private function _loadEntities()
    {
        $st = $this->execute(
            'SELECT * FROM '. self::$prefix .'hasEntity WHERE `uid` = ?;',
            array($this->_user->getUid())
        );

        if ($st === false) {
            return false;
        }

        $this->_entities = array();
        $rs = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rs AS $row) {
            $entity = new sspmod_janus_Entity($this->_config->getValue('store'));
            $entity->setEid($row['eid']);
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
    public function getEntities($force = false)
    {
        assert('is_bool($force);');

        if (empty($this->_entities) || $force) {
            if (!$this->_loadEntities()) {
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
        $entity = new sspmod_janus_Entity($this->_config->getValue('store'), true);
        $entity->setEntityid($entityid);
        $entity->setWorkflow($startstate);
        $entity->setType($type);
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
        foreach($this->_config->getValue('metadatafields.'.$type) AS $mk => $mv) {
            if(isset($mv['required']) && $mv['required'] === true)
            {
                $ec->addMetadata($mk, $mv['default']);
                $update = true;
            }
        }

        if($update === true)
        {
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
     * Retrive all active users in the system
     *
     * The method will retrive all active users in the system. NOTE this method will be
     * moved/rewritten in the future.
     *
     * @return array All active users in the system
     * @since  Method available since Release 1.2.0
     */
    public function getActiveUsers()
    {
        $st = $this->execute(
            'SELECT * FROM '. self::$prefix .'user WHERE `active` = ?;',
            array('yes')
        );

        return $st->fetchAll(PDO::FETCH_ASSOC);
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

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Erases all entities in database
     *
     * Erases all entities and related metadata, attributes and blocked
     * entities. NOTE this method is only for developing purpose
     *
     * @return True ALways return true, no matter if the DB calls fails or not
     */
    public function truncateDB()
    {
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'entity;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'hasEntity;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'metadata;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'attribute;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'blockedEntity;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'message;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'subscriptions;');
        $st = $this->execute('TRUNCATE TABLE '. self::$prefix .'tokens;');
        return true;
    }
}
?>
