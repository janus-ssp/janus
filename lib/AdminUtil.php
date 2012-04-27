<?php
/**
 * Administration utilities
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
/**
 * Administration utilities
 *
 * The class implements various utilities udes in the administrator interface
 * in the dashboard in JANUS. The functionality in this class will probably be
 * changed in the future. So do not rely on them to be valid if you are
 * extending JANUS.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @see        Sspmod_Janus_Database
 * @since      Class available since Release 1.0.0
 * @todo       Refactor this class
 * @todo       Put errorhandling into exceptions
 * @todo       Put most functions into Sspmod_Janus_Entity
 */
class sspmod_janus_AdminUtil extends sspmod_janus_Database
{
    /**
     * JANUS config
     * @var SimpleSAML_Configuration
     */
    private $_config;

    /**
     * Creates a new administrator utility.
     *
     * @since Method available since Release 1.0.0
     */
    public function __construct()
    {
        $this->_config = SimpleSAML_Configuration::getConfig('module_janus.php');

        // Send DB config to parent class
        parent::__construct($this->_config->getValue('store'));
    }

    /**
     * Retrive all entities from database
     *
     * The method retrives all entities from the database together with the
     * newest revision id.
     *
     * @param array|string $state States requesting
     * @param array|string $type  Types requesting
     *
     * @return false|array All entities from the database
     */
    public function getEntitiesByStateType($state = null, $type = null, $active = 'yes')
    {

        if (!is_null($state) && !is_array($state)) {
            $state = array($state);
        }

        if (!is_null($type) && !is_array($type)) {
            $type = array($type);
        }

        $sql = array();
        $params = array();

        if (!empty($state)) {
            $sql[1] = '`state` = ?';
            $params = array_merge($params, $state);
        } 
        
        if (!empty($type)) {
            $sql[2] = '`type` IN ('. implode(
                ',', array_fill(0, count($type), '?')
            ) . ')';
            $params = array_merge($params, $type);
        } 
        
        $params[] = $active;
        
        $st = self::execute('
            SELECT T.`eid`, T.`entityid`, T.`revisionid`, T.`created` 
            FROM `'. self::$prefix .'entity` AS T, (
                SELECT `eid`, MAX(`revisionid`) AS `revisionid`, `created` 
                FROM `'. self::$prefix .'entity` 
                WHERE ' . implode(' AND ', $sql) . ' AND `active` = ? 
                GROUP BY `eid`
            ) AS M
            WHERE T.`revisionid` = M.`revisionid` AND T.`eid` = M.`eid`;',
            $params    
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }


    /**
     * Retrive all entities from database
     *
     * The method retrives all entities from the database together with the
     * newest revision id.
     *
     * @return false|array All entities from the database
     */
    public function getEntities()
    {
        $st = self::execute(
            'SELECT `eid`, `entityid`, MAX(`revisionid`) AS `revisionid`,
                `created`
            FROM `'. self::$prefix .'entity`
            GROUP BY `eid`;'
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }

    /**
     * Returns an array user ids that have permission to see the given entity
     *
     * @param string $eid The entity whose parmissions is to be returned
     *
     * @return bool|array The users that have permission to see the entity
     *
     * @since Method available since Release 1.0.0
     * @TODO Rename to getPermission or similar
     */
    public function hasAccess($eid)
    {
        assert('is_string($eid)');

        $st = self::execute(
            'SELECT t3.`uid`, t3.`userid`
            FROM `'. self::$prefix .'hasEntity` AS t2,
            `'. self::$prefix .'user` AS t3
            WHERE t3.active = ? AND t2.uid = t3.uid AND t2.`eid` = ?;',
            array('yes', $eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }

    /**
     * Returns an array user ids that do not have permission to see the given
     * entity
     *
     * @param string $eid The entity whose parmissions is to be returned
     *
     * @return bool|array The users that do not have permission to see the
     * entity
     *
     * @since Method available since Release 1.0.0
     * @TODO Rename to getNegativePermission or similar
     */
    public function hasNoAccess($eid)
    {
        assert('is_string($eid)');

        $st = self::execute(
            'SELECT DISTINCT(u.`uid`), u.`userid`
            FROM `'. self::$prefix .'user` AS u
            WHERE u.`uid` NOT IN (
                SELECT uid
                FROM `'. self::$prefix .'hasEntity`
                WHERE `eid` = ?
            ) AND u.`active` = ?;',
            array($eid, 'yes')
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }

    /**
     * Removes the specified users from the entity
     *
     * @param string $eid The entity
     * @param string $uid The user to be removed from the entity
     *
     * @return bool True on success and false on error
     *
     * @since Method available since Release 1.0.0
     * @TODO Rename to removePermission or similar
     */
    public function removeUserFromEntity($eid, $uid)
    {
        $st = self::execute(
            'DELETE FROM `'. self::$prefix .'hasEntity`
            WHERE `eid` = ? AND `uid` = ?;',
            array($eid, $uid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error removing the entity-user');
            return false;
        }

        return true;
    }

    /**
     * Get entities from specified user
     *
     * @param string $uid The user
     *
     * @return array on success and false on error
     * @since Method available since Release 1.2.0
     */
    public function getEntitiesFromUser($uid)
    {
        $st = self::execute(
            'SELECT `eid` FROM `'. self::$prefix .'hasEntity`
            WHERE `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
             SimpleSAML_Logger::error('JANUS: Error returning the entities-user');
             return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }

    /**
     * Remove all entities from a user
     *
     * @param string $uid The user to be removed from the entity
     *
     * @return bool True on success and false on error
     *
     * @since Method available since Release 1.2.0
     */
    public function removeAllEntitiesFromUser($uid)
    {
        $st = self::execute(
            'DELETE FROM `'. self::$prefix .'hasEntity`
            WHERE  `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error removing all entities-user');
            return false;
        }

        return true;
    }

    /**
     * Add the specified users to the entity
     *
     * @param string $eid The entity
     * @param string $uid The user to be added to the entity
     *
     * @return bool True on success and false on error
     * @since Method available since Release 1.0.0
     * @TODO Rename to addPermission or similar
     */
    public function addUserToEntity($eid, $uid)
    {
        $st = self::execute(
            'INSERT INTO `'. self::$prefix .'hasEntity`
                (`uid`, `eid`, `created`, `ip`)
            VALUES
                (?, ?, ?, ?);',
            array($uid, $eid, date('c'), $_SERVER['REMOTE_ADDR'])
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $user = new sspmod_janus_User($this->_config->getValue('store'));
        $user->setUid($uid);
        $user->load();
        $userid = $user->getUserid();

        return $userid;
    }

    /**
     * Retrive the enabled entity types
     *
     * @return array Contains the enabled entitytypes
     * @since Methos available since Release 1.0.0
     */
    public function getAllowedTypes()
    {
        $config = $this->_config;
        $enablematrix = array(
            'saml20-sp' => array(
                'enable' => $config->getBoolean('enable.saml20-sp', false),
                'name' => 'SAML 2.0 SP',
            ),
            'saml20-idp' => array(
                'enable' => $config->getBoolean('enable.saml20-idp', false),
                'name' => 'SAML 2.0 IdP',
            ),
            'shib13-sp' => array(
                'enable' => $config->getBoolean('enable.shib13-sp', false),
                'name' => 'Shib 1.3 SP',
            ),
            'shib13-idp' => array(
                'enable' => $config->getBoolean('enable.shib13-idp', false),
                'name' => 'Shib 1.3 IdP',
            ),
        );

        return $enablematrix;
    }

    /**
     * Delete an entity from the database
     *
     * @param int $eid The entitys Eid
     *
     * @return void
     * @since Methos available since Release 1.0.0
     */
    public function deleteEntity($eid)
    {
        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'entity
            WHERE `eid` = ?;',
            array($eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }

        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'hasEntity
            WHERE `eid` = ?;',
            array($eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }

        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'metadata
            WHERE `eid` = ?;',
            array($eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }

        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'attribute
            WHERE `eid` = ?;',
            array($eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }

        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'blockedEntity
            WHERE `eid` = ?;',
            array($eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }
        
        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'subscription
            WHERE `subscription` = ?;',
            array('ENTITYUPDATE-'.$eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Not all revisions of entity deleted.'
            );
        }

        return;
    }

    /**
     * Get a complete list of all ARPs in the system
     *
     * @return array
     */
    public function getARPList()
    {
        $arp = new sspmod_janus_ARP;
        return $arp->getARPlist();
    }

    /**
     * Disable an entity from the database
     *
     * @param int $eid The entitys Eid
     *
     * @return void
     * @since Methos available since Release 1.11.0
     */
    public function disableEntity($eid)
    {
        $st = $this->execute(
            'UPDATE `'. self::$prefix .'entity` SET `active` = ?
            WHERE `eid` = ?;',
            array('no', $eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:disableEntity - Not all revisions of entity was disabled.'
            );
        }

        return;
    }
    
    /**
     * Enable an entity from the database
     *
     * @param int $eid The entitys Eid
     *
     * @return void
     * @since Methos available since Release 1.11.0
     */
    public function enableEntity($eid)
    {
        $st = $this->execute(
            'UPDATE `'. self::$prefix .'entity` SET `active` = ?
            WHERE `eid` = ?;',
            array('yes', $eid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error(
                'JANUS:disableEntity - Not all revisions of entity was enabled.'
            );
        }

        return;
    }
}
?>
