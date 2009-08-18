<?php
/**
 * Administration utilities 
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
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
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
     * @return false|array All entities from the database
     */
    public function getEntities()
    {
        $st = self::execute(
            'SELECT `eid`, `entityid`, MAX(`revisionid`) AS `revisionid`, 
                `created`  
            FROM `'. self::$prefix .'entity` 
            GROUP BY `entityid`;'
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
            'SELECT t3.`uid`, t3.`email` 
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
     *
     */
    public function hasNoAccess($eid)
    {
        assert('is_string($eid)');

        $st = self::execute(
            'SELECT DISTINCT(t3.`uid`), t3.`email` 
            FROM `'. self::$prefix .'hasEntity` AS t2, 
                `'. self::$prefix .'user` AS t3
            WHERE t3.`uid` NOT IN (
                SELECT uid
                FROM `'. self::$prefix .'hasEntity`
                WHERE `eid` = ?				
            ) AND t3.`active` = ?;', 
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
     *
     */
    public function removeUserFromEntity($eid, $uid)
    {
        $st = self::execute(
            'DELETE FROM `'. self::$prefix .'hasEntity` 
            WHERE `eid` = ? AND `uid` = ?;', 
            array($eid, $uid)
        );

        if ($st === false) {
            SimpleSAML_Logger::error('JANUS: Error fetching all entities');
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
     * 
     * @since Method available since Release 1.0.0  
     * @TODO Rename to addPermission or similar
     *
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
        $email = $user->getEmail();

        return $email;
    }

    public function getAllowedTypes() {
        $config = SimpleSAML_Configuration::getInstance();
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
                'enable' => $config->getBoolean('enable.shub13-idp', false),
                'name' => 'Shib 1.3 IdP',
            ),
        );

        return $enablematrix;
    }

}
?>
