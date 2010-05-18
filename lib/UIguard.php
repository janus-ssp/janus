<?php
/**
 * User Interface Guard
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
 * @since      File available since Release 1.1.0
 */
/**
 * User Interface Guard
 *
 * Implements a basic guard for manageging access to UI elements. The
 * permissions are parsed to the constructor and is set in the JANUS config
 * file. NOTE that this implementation is not complete yet, so expect changes in the
 * future.
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
class sspmod_janus_UIguard
{
    /**
     * Permission matrix
     * @var array
     */
    private $_permissionmatrix;

    /**
     * Create a new UIGuard
     *
     * @param array $permissionmatrix Permissions to apply
     */
    public function __construct($permissionmatrix)
    {
        $this->_permissionmatrix = $permissionmatrix;
    }

    /**
     * Check if a given usertype has permission to access a given element.
     *
     * The method is parsed an UI element, a state, a usertype and will return
     * true or false depending on wether the usertype has permission to access
     * the given element.
     * The configuration of the permissions has to be defined in the
     * configuration file.
     *
     * @param string $element  A string representing the UI element
     * @param string $state    A string representing the state
     * @param string $usertype A string representing the usertype
     * @param bool   $global   If set to true the state is ignored
     *
     * @return bool True if the usertype has permission to access the element
     * @throws SimpleSAML_Error_Exception Throwed if the no permission is
     * defined for the given element.
     * @since      Method available since Release 1.1.0
     */
    public function hasPermission($element, $state = null, $usertype, $global = false)
    {
        // Arraize usertype        
        $types_neg = array();
        foreach($types AS $type) {
            $types_neg[] = '-' . $type;
        }
        $types_neg[] = '-all';

        // Get correct permission matrix
        if($global == true) {
            if(!isset($this->_permissionmatrix[$element]['role'])) {
                throw new SimpleSAML_Error_Exception('No global role defined on element ' . $element); 
            }
            $permissions = $this->_permissionmatrix[$element]['role'];
        } else if(isset($this->_permissionmatrix[$element][$state])) {
            if(!isset($this->_permissionmatrix[$element][$state]['role'])) {
                throw new SimpleSAML_Error_Exception('No role defined for state ' . $state . ' on element ' . $element);
            }
            $permissions = $this->_permissionmatrix[$element][$state]['role'];
        } else if (isset($this->_permissionmatrix[$element]['default'])) {
             // Return default permission for element
            return (bool)$this->_permissionmatrix[$element]['default'];
        } else {
            throw new SimpleSAML_Error_Exception('No default value for element ' . $element . ' given');
        }

        $intersect = array_intersect($types, $permissions);
        $intersect_neg = array_intersect($types_neg, $permissions);
    
        if (!empty($intersect)) {
            // User type is allowed
            return true;
        } else if (!empty($intersect_neg)) {
            // User type is disallowed
            return false;
        } else if (in_array('all', $permissions)) {
            // All user types are allowed
            return true;
        } else {
            // Usertype do not have permission
            return false;
        }
    }
}
?>
