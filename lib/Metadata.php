<?php
/**
 * Metadata element
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
 * Metadata element
 *
 * The class implements basic functionality regarding creating and updating 
 * metadata elements.
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
 * @todo      Change entityid to id as connection to entity
 */
class sspmod_janus_Metadata extends sspmod_janus_Database
{
    /**
     * Entity id
     * @var string
     */
    private $_entityid;

    /**
     * Revision id
     * @var int
     */
    private $_revisionid;

    /**
     * Metadata key
     * @var string
     */
    private $_key;

    /**
     * Metadata value
     * @var string
     */
    private $_value;

    /**
     * Modify status for the metadata
     * @var bool
     */
    private $_modified = false;

    /**
     * Creates a new instanse of matadata
     *
     * @param SimpleSAML_Configuration &$config Configuration for JANUS
     *
     * @since Class available since Release 1.0.0
     */
    public function __construct(&$config)
    {
        parent::__construct($config);
    }

    /**
     * Load metadata
     *
     * Load the metadata from database. The entity id, revision id and the key 
     * must be set.
     *
     * @return PDOStatement|false The satatement or false on error
     * @since Class available since Release 1.0.0
     */
    public function load()
    {
        if (   empty($this->_entityid) 
            || is_null($this->_revisionid) 
            || empty($this->_key)
        ) {
            SimpleSAML_Logger::error(
                'JANUS:Metadata:load - entityid and revisionid needs to be set.'
            );
            return false;
        }

        $st = $this->execute(
            'SELECT * 
            FROM '. self::$prefix .'__metadata 
            WHERE `entityid` = ? AND `revisionid` = ? AND `key` = ?;', 
            array($this->_entityid, $this->_revisionid, $this->_key)
        );
        if ($st === false) {
            return false;
        }

        while ($row = $st->fetchAll(PDO::FETCH_ASSOC)) {
            $this->_value = $row['0']['value'];

            $this->_modified = false;
        }
        return $st;
    }

    /**
     * Save metadata
     *
     * Save the metadata to database. Entity id and key must be set. Nothing is 
     * written to database, if no modifications have been made.
     *
     * @return PDOStatement|false The statement or false on error.
     * @since Class available since Release 1.0.0
     */
    public function save()
    {
        if (!$this->_modified) {
            return true;
        }
        if (!empty($this->_entityid) && !empty($this->_key)) {
            $st = $this->execute(
                'INSERT INTO '. self::$prefix .'__metadata 
                (`entityid`, `revisionid`, `key`, `value`, `created`, `ip`) 
                VALUES 
                (?, ?, ? ,?, ?, ?);',
                array(
                    $this->_entityid, 
                    $this->_revisionid, 
                    $this->_key, 
                    $this->_value, 
                    date('c'), 
                    $_SERVER['REMOTE_ADDR']
                )
            );

            if ($st === false) {
                return false;
            }
        } else {
            return false;
        }

        return $st;
    }

    /**
     * Set entity id
     *
     * @param string $entityid Entity id
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setEntityid($entityid)
    {
        assert('is_string($entityid)');

        $this->_entityid = $entityid;

        $this->_modified = true;
    }

    /**
     * Set revision id
     *
     * @param int $revisionid Revision id
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setRevisionid($revisionid)
    {
        assert('ctype_digit((string) $revisionid);');

        $this->_revisionid = $revisionid;

        $this->_modified = true;
    }

    /**
     * Set metadata key
     *
     * @param string $key Metadata key
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setKey($key)
    {
        assert('is_string($key)');

        $this->_key = $key;

        $this->_modified = true;
    }

    /**
     * Set metadata value
     *
     * @param string $value Metadata value
     *
     * @return void
     * @since Class available since Release 1.0.0
     */
    public function setValue($value)
    {
        assert('is_string($value)');

        $this->_value = $value;

        $this->_modified = true;
    }

    /**
     * Get entity id
     *
     * @return string Entity id
     * @since Class available since Release 1.0.0
     */
    public function getEntityid()
    {
        return $this->_entityid;
    }

    /**
     * Get revision id
     *
     * @return int Revision id
     * @since Class available since Release 1.0.0
     */
    public function getRevisionid()
    {
        return $this->_revisionid;
    }

    /**
     * Get metadata key
     *
     * @return string Metadata key
     * @since Class available since Release 1.0.0
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get metadata value
     *
     * @return string Metadata value
     * @since Class available since Release 1.0.0
     */
    public function getValue()
    {
        return $this->_value;
    }
}
?>
