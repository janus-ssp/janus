<?php
/**
 * User Interface Guard
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
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
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
     * @since Method available since Release 1.1.0
     * @throws SimpleSAML_Error_Exception Throws if the no permission is defined for the given element.
     *
     * @param string        $element  A string representing the UI element
     * @param string|null   $state    A string representing the state
     * @param array         $types    A string representing the roles of the user
     * @param bool          $global   If set to true the state is ignored
     * @return bool True if the usertype has permission to access the element
     */
    public function hasPermission($element, $state = null, array $types, $global = false)
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
                return false;
            }
            $permissions = $this->_permissionmatrix[$element]['role'];
        } else if(isset($this->_permissionmatrix[$element][$state])) {
            if(!isset($this->_permissionmatrix[$element][$state]['role'])) {
                return false;
            }
            $permissions = $this->_permissionmatrix[$element][$state]['role'];
        } else if (isset($this->_permissionmatrix[$element]['default'])) {
             // Return default permission for element
            return (bool)$this->_permissionmatrix[$element]['default'];
        } else {
            return false;
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
