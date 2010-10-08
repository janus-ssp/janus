<?php
/**
 * ARP object
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
 * ARP object
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
class sspmod_janus_ARP extends sspmod_janus_Database
{
    /**
     * Aid
     * @var string
     */
    private $_aid;

    private $_name;
    private $_description;
    private $_attributes = array();

    /**
     * Modify status for the ARP
     * @var bool
     */
    private $_modified = false;

    /**
     * Creates a new ARP
     *
     * @param SimpleSAML_Configuration $config Configuration for database
     */
    public function __construct()
    {
        $this->_config = SimpleSAML_Configuration::getConfig('module_janus.php');

        // Send DB config to parent class
        parent::__construct($this->_config->getValue('store'));
    }

    public function delete() {
        if (empty($this->_aid)) {
            SimpleSAML_Logger::error(
                'JANUS:ARP:delete - aid needs to be set.'
            );
            return false;
        }
    
        $st = $this->execute(
            'DELETE FROM '. self::$prefix .'arp
            WHERE `aid` = ?;',
            array($this->_aid)
        );

        if ($st === false) {
            return false;
        }

        return $st;
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
        if (empty($this->_aid)) {
            SimpleSAML_Logger::error(
                'JANUS:ARP:load - aid needs to be set.'
            );
            return false;
        }

        $st = $this->execute(
            'SELECT * FROM '. self::$prefix .'arp
            WHERE `aid` = ?;',
            array($this->_aid)
        );

        if ($st === false) {
            return false;
        }

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        if(empty($rows)) {
            return false;
        }

        // Fetch the valu and save it in the object
        foreach ($rows AS $row) {
            $this->_name = $row['name'];
            $this->_description = $row['description'];
            $this->_attributes = unserialize($row['attributes']);
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

        if (!empty($this->_aid)) {
            // Save existing arp
            $st = $this->execute(
                'UPDATE '. self::$prefix .'arp SET 
                    `name` = ?,
                    `description` = ?,
                    `attributes` = ?,
                    `updated` = ?,
                    `ip` = ?
                WHERE `aid` = ?;',
                array(
                    $this->_name,
                    $this->_description,
                    serialize($this->_attributes),
                    date('c'),
                    $_SERVER['REMOTE_ADDR'],
                    $this->_aid,
                )
            );

            if ($st === false) {
                return false;
            }
        } else {
            // Inters new ARP
            $st = $this->execute(
                'INSERT INTO '. self::$prefix .'arp
                    (`aid`, `name`, `description`, `attributes`, `created`, `updated`, `ip`)
                VALUES (NULL, ?, ? ,?, ?, ?, ?);',
                array(
                    $this->_name,
                    $this->_description,
                    serialize($this->_attributes),
                    date('c'),
                    date('c'),
                    $_SERVER['REMOTE_ADDR'],
                )
            );

            if ($st === false) {
                return false;
            }
            
            $this->_aid = self::$db->lastInsertId();
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
    public function setAid($aid)
    {
        assert('is_string($aid)');

        $this->_aid = $aid;
        $this->_modified = true;
    }

    /**
     * Returns the entity id associated with the attribute
     *
     * @return string The entity id
     */
    public function getAid()
    {
        return $this->_aid;
    }
    
    public function setName($name) {
        assert('is_string($name)');

        $this->_name = $name;
        $this->_modified = true;
    }

    public function getName() {
        return $this->_name;
    }

    public function setDescription($description) {
        assert('is_string($description)');

        $this->_description = $description;
        $this->_modified = true;
    }

    public function getDescription() {
        return $this->_description;
    } 

    public function setAttributes($attributes) {
        assert('is_array($attributes)');

        $this->_attributes = $attributes;
        $this->_modified = true;
    }

    public function getAttributes() {
        if(empty($this->_attributes)) {
            return $this->_config->getArray('entity.defaultarp', array());
        }

        return $this->_attributes;
    } 

    public function getARPList() {
        $st = $this->execute(
            'SELECT * FROM '. self::$prefix .'arp;',
            array()
        );

        if ($st === false) {
            return false;
        }

        // Fetch the valu and save it in the object
        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        
        array_unshift($row, array("aid"=> '0', "name" => "No ARP", "description" =>  "No ARP"));

        return $row;
    }
}
?>
