<?php

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

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
 * @link       http://github.com/janus-ssp/janus/
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
 * @link       http://github.com/janus-ssp/janus/
 * @see        Sspmod_Janus_Database
 * @since      Class available since Release 1.0.0
 * @todo       Move methods to connection and user services
 */
class sspmod_janus_AdminUtil extends sspmod_janus_Database
{
    /**
     * JANUS config
     * @var ConfigProxy
     */
    private $_config;

    /**
     * Creates a new administrator utility.
     *
     * @since Method available since Release 1.0.0
     */
    public function __construct()
    {
        $this->_config = sspmod_janus_DiContainer::getInstance()->getConfig();
    }

    /**
     * Retrieve all entities from database
     *
     * The method retrieves all entities from the database together with the
     * newest revision id.
     *
     * @param array|string $state States requesting
     * @param array|string $type  Types requesting
     *
     * @return bool|array All entities from the database
     */
    public function getEntitiesByStateType($state = null, $type = null, $active = 'yes')
    {
        $state = (array)$state;
        $type  = (array)$type;

        $whereClauses = array(
            '`active` = ?'
        );
        $queryData = array($active);

        if (!empty($state)) {
            $placeHolders = array_fill(0, count($state), '?');
            $whereClauses[] = 'CONNECTION_REVISION.state IN ('. implode(',', $placeHolders). ')';
            $queryData = array_merge($queryData, $state);
        } 
        
        if (!empty($type)) {
            $placeHolders = array_fill(0, count($type), '?');
            $whereClauses[] = 'CONNECTION_REVISION.type IN ('. implode(',', $placeHolders). ')';
            $queryData = array_merge($queryData, $type);
        }

        // Select entity (only last revision)
        $selectFields = array(
            'DISTINCT CONNECTION_REVISION.eid',
            'CONNECTION_REVISION.entityid',
            'CONNECTION_REVISION.revisionid',
            'CONNECTION_REVISION.created',
            'CONNECTION_REVISION.state',
            'CONNECTION_REVISION.type',
        );
        $fromTable = $this->getTablePrefix() . "connection AS CONNECTION";
        $joins = array("
            INNER JOIN " . $this->getTablePrefix() . "connectionRevision AS CONNECTION_REVISION
                ON CONNECTION_REVISION.eid = CONNECTION.id
                AND CONNECTION_REVISION.revisionid = CONNECTION.revisionNr
        ");
        $orderFields = array('created ASC');

        // Find default value for sort field so it can be excluded
        /** @var $sortFieldName string */
        $sortFieldName = $this->_config->getString('entity.prettyname', NULL);
        // Try to sort results by pretty name from metadata
        if ($sortFieldName) {
            $fieldDefaultValue = '';
            if ($sortFieldDefaultValue = $this->_config->getArray('metadatafields.saml20-idp', FALSE)) {
                if (isset($sortFieldDefaultValue[$sortFieldName])) {
                    $fieldDefaultValue = $sortFieldDefaultValue[$sortFieldName]['default'];
                }
            } else if ($sortFieldDefaultValue = $this->_config->getArray('metadatafields.saml20-sp', FALSE)) {
                if (isset($sortFieldDefaultValue[$sortFieldName])) {
                    $fieldDefaultValue = $sortFieldDefaultValue[$sortFieldName]['default'];
                }
            }
            $joins[] = "
            LEFT JOIN   " . $this->getTablePrefix() . "metadata AS METADATA
                ON METADATA.key = ?
                AND METADATA.connectionRevisionId = CONNECTION_REVISION.id
                AND METADATA.value != ?";

            array_unshift($queryData, $fieldDefaultValue);
            array_unshift($queryData, $sortFieldName);
            $selectFields[] = 'IFNULL(METADATA.`value`, CONNECTION_REVISION.`entityid`) AS `orderfield`';
            $orderFields = array("orderfield ASC");
        }

        $query = 'SELECT ' . implode(', ', $selectFields);
        $query .= "\nFROM " . $fromTable;
        $query .= implode("\n", $joins);
        $query .= "\nWHERE " . implode(' AND ', $whereClauses);
        $query .= "\nORDER BY " . implode(', ', $orderFields);

        $st = self::execute($query, $queryData);
        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }


    /**
     * Retrieve all entities from database
     *
     * The method retrieves all entities from the database together with the
     * newest revision id.
     *
     * @return bool|array All entities from the database
     */
    public function getEntities()
    {
        $st = self::execute(
            'SELECT id AS `eid`,
                    name AS `entityid`,
                    revisionNr AS `revisionid`,
                    `created`
            FROM `'. $this->getTablePrefix() .'connection`;'
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching all entities');
            return false;
        }

        $rs = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rs;
    }

    /**
     * Returns an array user ids that have permission to see the given entity
     *
     * @param string $eid The entity whose permissions are to be returned
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
            FROM `'. $this->getTablePrefix() .'hasConnection` AS t2,
            `'. $this->getTablePrefix() .'user` AS t3
            WHERE t3.active = ? AND t2.uid = t3.uid AND t2.`eid` = ?;',
            array('yes', $eid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching all entities');
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
            FROM `'. $this->getTablePrefix() .'user` AS u
            WHERE u.`uid` NOT IN (
                SELECT uid
                FROM `'. $this->getTablePrefix() .'hasConnection`
                WHERE `eid` = ?
            ) AND u.`active` = ?;',
            array($eid, 'yes')
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error fetching all entities');
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
            'DELETE FROM `'. $this->getTablePrefix() .'hasConnection`
            WHERE `eid` = ? AND `uid` = ?;',
            array($eid, $uid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error removing the entity-user');
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
        $query = 'SELECT CONNECTION_REVISION.*
            FROM '. $this->getTablePrefix() .'connection CONNECTION
            INNER JOIN '. $this->getTablePrefix() .'connectionRevision CONNECTION_REVISION
                ON CONNECTION_REVISION.eid = CONNECTION.id
                AND CONNECTION_REVISION.revisionid = CONNECTION.revisionNr
            JOIN '. $this->getTablePrefix() .'hasConnection jhe ON jhe.eid = CONNECTION_REVISION.eid
            WHERE jhe.uid = ?';
        $st = self::execute($query, array($uid));

        if ($st === false) {
             \SimpleSAML\Logger::error('JANUS: Error returning the entities-user');
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
            'DELETE FROM `'. $this->getTablePrefix() .'hasConnection`
            WHERE  `uid` = ?;',
            array($uid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error('JANUS: Error removing all entities-user');
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
     * @return string username
     * @throws Exception
     * @since Method available since Release 1.0.0
     * @TODO Rename to addPermission or similar
     */
    public function addUserToEntity($eid, $uid)
    {
        $user = $this->getUserService()->findById($uid);

        $connectionService = $this->getConnectionService();
        $connection = $connectionService->findById($eid);

        $connectionService->allowAccess($connection, $user);

        return $user->getUsername();
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
        $this->getConnectionService()->deleteById($eid);
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
            'UPDATE `'. $this->getTablePrefix() .'connectionRevision` SET `active` = ?
            WHERE `eid` = ?;',
            array('no', $eid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error(
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
            'UPDATE `'. $this->getTablePrefix() .'connectionRevision` SET `active` = ?
            WHERE `eid` = ?;',
            array('yes', $eid)
        );

        if ($st === false) {
            \SimpleSAML\Logger::error(
                'JANUS:disableEntity - Not all revisions of entity was enabled.'
            );
        }

        return;
    }

    /**
     * Given an entity (like a SAML2 SP) and a list of remote entities (like a set of SAML2 IdPs)
     * find out which of those remote entities do not allow the entity to connect.
     *
     * @param sspmod_janus_Entity   $entity
     * @param array                 $remoteEntities
     */
    public function getReverseBlockedEntities(sspmod_janus_Entity $entity, array $remoteEntities)
    {
        if (empty($remoteEntities)) {
            return array();
        }

        $remoteEids = array();
        foreach ($remoteEntities as $remoteEntity) {
            $remoteEids[] = $remoteEntity['eid'];
        }

        $queryParams = array($entity->getEid(), $entity->getEid());
        $queryParams = array_merge($queryParams, $remoteEids);

        $queryEidsIn = implode(', ', array_fill(0, count($remoteEids), '?'));
        $tablePrefix = $this->getTablePrefix();
        $query = <<<"SQL"
SELECT  eid,
        entityid,
        revisionid,
        state,
        type
FROM (
    SELECT CONNECTION_REVISION.eid,
           CONNECTION_REVISION.entityid,
           CONNECTION_REVISION.revisionid,
           CONNECTION_REVISION.state,
           CONNECTION_REVISION.type,
           CONNECTION_REVISION.allowedall,
           (SELECT COUNT(*) > 0 FROM {$tablePrefix}allowedConnection WHERE connectionRevisionId = CONNECTION_REVISION.id) AS uses_whitelist,
           (SELECT COUNT(*) > 0 FROM {$tablePrefix}blockedConnection WHERE connectionRevisionId = CONNECTION_REVISION.id) AS uses_blacklist,
           (SELECT COUNT(*) > 0 FROM {$tablePrefix}allowedConnection WHERE connectionRevisionId = CONNECTION_REVISION.id AND remoteeid = ?) AS in_whitelist,
           (SELECT COUNT(*) > 0 FROM {$tablePrefix}blockedConnection WHERE connectionRevisionId = CONNECTION_REVISION.id AND remoteeid = ?) AS in_blacklist
    FROM {$tablePrefix}connection CONNECTION
    INNER JOIN {$tablePrefix}connectionRevision CONNECTION_REVISION
        ON CONNECTION_REVISION.eid = CONNECTION.id
        AND CONNECTION_REVISION.revisionid = CONNECTION.revisionNr
    WHERE CONNECTION.id IN ($queryEidsIn)
   ) AS LATEST_REVISION
WHERE allowedall = 'no'
  AND (
      (uses_whitelist = TRUE AND in_whitelist = FALSE)
        OR (uses_blacklist = TRUE AND in_blacklist = TRUE)
        OR (uses_blacklist = FALSE AND uses_whitelist = FALSE)
  )
SQL;

        $statement = $this->execute($query , $queryParams);
        return $statement->fetchAll();
    }
}
