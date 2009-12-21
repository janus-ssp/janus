<?php
/**
 * An attribute
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
 */
/**
 * An attribute
 *
 * This class is a basic implementation of an attribute used in JANUS. The
 * attribute are connected to an entity nad has different meaning depending on
 * what type the entity is.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 */
class sspmod_janus_Attribute extends sspmod_janus_Database
{
    /**
     * Eid
     * @var string
     */
    private $_eid;

    /**
     * The revision number
     * @var int
     */
    private $_revisionid;

    /**
     * Attribute key
     * @var string
     */
    private $_key;

    /**
     * Attribute value
     * @var string
     */
    private $_value;

    /**
     * Modify status for the attribute
     * @var bool
     */
    private $_modified = false;

    /**
     * Creates a new attribute
     *
     * @param SimpleSAML_Configuration $config Configuration for database
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Load attribute from database
     *
     * The entity id, revision id and key must be set before calling this
     * method.
     *
     * @return PDOStatement|false The statement or false on error.
     * @see PHP_MANUAL#PDOStatement
     */
    public function load()
    {
        // Check that the eid, revisionid and key is set
        if (   empty($this->_eid)
            || is_null($this->_revisionid)
            || empty($this->_key)
        ) {
            SimpleSAML_Logger::error(
                'JANUS:Attribute:load - eid and revisionid needs to be set.'
            );
            return false;
        }

        $st = $this->execute(
            'SELECT * FROM '. self::$prefix .'attribute
            WHERE `eid` = ? AND `revisionid` = ? AND `key` = ?;',
            array($this->_eid, $this->_revisionid, $this->_key)
        );

        if ($st === false) {
            return false;
        }

        // Fetch the valu and save it in the object
        while ($row = $st->fetchAll(PDO::FETCH_ASSOC)) {
            $this->_value = $row['0']['value'];
            $this->_modified = false;
        }

        return $st;
    }

    /**
     * Save attribute.
     *
     * Stores the attribute in the database. The entity id, revision id and key
     * needs to be set before calling this method.
     *
     * @return bool TRUE on success and FALSE on error
     */
    public function save()
    {
        // Has the sttribute been modified?
        if (!$this->_modified) {
            return true;
        }

        // Is eid and key set
        if (   !empty($this->_eid)
            && !empty($this->_key)
            && !empty($this->_revisionid)
        ) {
            $st = $this->execute(
                'INSERT INTO '. self::$prefix .'attribute
                    (`eid`, `revisionid`, `key`, `value`, `created`, `ip`)
                VALUES (?, ?, ? ,?, ?, ?);',
                array(
                    $this->_eid,
                    $this->_revisionid,
                    $this->_key,
                    $this->_value,
                    date('c'),
                    $_SERVER['REMOTE_ADDR'],
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
     * Set the entity id of the attribute.
     *
     * @param string $eid The entity id
     *
     * @return void
     */
    public function setEid($eid)
    {
        assert('is_string($eid)');

        $this->_eid = $eid;
        $this->_modified = true;
    }

    /**
     * Set the revision id of the attribute
     *
     * @param int $revisionid The revision id
     *
     * @return void
     * @todo Rename method to setRevisionId
     */
    public function setRevisionid($revisionid)
    {
        assert('ctype_digit((string) $revisionid);');

        $this->_revisionid = $revisionid;
        $this->_modified = true;
    }

    /**
     * Set the attribute key.
     *
     * @param string $key The attribute key
     *
     * @return void
     */
    public function setKey($key)
    {
        assert('is_string($key)');

        $this->_key = $key;
        $this->_modified = true;
    }

    /**
     * Set the attribute value
     *
     * @param string $value The attribute value
     *
     * @return void
     */
    public function setValue($value)
    {
        assert('is_string($value)');

        $this->_value = $value;
        $this->_modified = true;
    }

    /**
     * Returns the entity id associated with the attribute
     *
     * @return string The entity id
     */
    public function getEid()
    {
        return $this->_eid;
    }

    /**
     * Returns the revision id associated with the attribute
     *
     * @return int Revision id
     * @todo Rename method to getRevisionId
     */
    public function getRevisionid()
    {
        return $this->_revisionid;
    }

    /**
     * Returns the attribute key
     *
     * @return string The attribute key
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Returns the attribute value
     *
     * @return string The attribute value
     */
    public function getValue()
    {
        return $this->_value;
    }
}
?>
