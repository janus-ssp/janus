<?php
/**
 * An AccessBlocker processing filter
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
 * @subpackage ProcessingFilter
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
/**
 * An AccessBlocker processing filter
 *
 * This filter is a basic implementation of an access blocking processing 
 * filter for simpleSAMLphp. The implementation is user in JANUS. The filter is 
 * configured in either the global config file or in the metadata files for the 
 * entity. 
 * <code>
 * 10 => array(
 *    'class' => 'janus:AccessBlocker',
 *    'blocked' => array(
 *       'entity1',
 *       'entity2',
 *       ...
 *    ),
 * ),
 * </code>
 * 
 * The filter will block access to the entities given in the configuration.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage ProcessingFilter
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link       http://code.google.com/p/janus-ssp/
 * @see        SimpleSAML_Auth_ProcessingFilter
 * @since      Release 1.0.0
 */
class sspmod_janus_Auth_Process_AccessBlocker extends SimpleSAML_Auth_ProcessingFilter
{
    /**
     * Array of blocked entities
     * @var array
     */
    private $_blocked = array();

    /**
     * Initialize this filter
     *
     * @param array $config   The configuration information about this filter
     * @param mixed $reserved For future use
     *
     * @throws SimpleSAML_Error_Exception
     * @since Release 1.0.0
     */
    public function __construct($config, $reserved)
    {
        assert('is_array($config)');

        // Call parent constructor
        parent::__construct($config, $reserved);

        // Process config array
        foreach ($config AS $name => $value) {
            if (!is_string($name)) {
                throw new SimpleSAML_Error_Exception(
                    'Config parameter must be string in janus:AccessBlocker: ' 
                    . var_export($name, true));
            }

            // If parameter is `blocked`
            if ($name === 'blocked') {
                if (!is_array($value) && is_string($value)) {
                    $this->_blocked = array($value);
                } else {
                    $this->_blocked = $value;
                }
            } else {
                new SimpleSAML_Error_Exception(
                    'Invalid config parameter given to janus:AccessBlocker: ' 
                    . var_export($name, true));
            }
        }
    }

    /**
     * Apply filter to block entities
     *
     * Stop the current authentication request if either the Identity Provider
     *  or the Service Provider is set to be blocked by the configuration.
     *
     * @param array &$state The current state
     *
     * @return void 
     * @since Release 1.0.0
     */
    public function process(&$state)
    {
        assert('is_array($state)');

        $session = SimpleSAML_Session::getInstance();

        // Get the IdP
        $remote_entity_idp = $session->getIdP();
        // Get the SP
        $remote_entity_sp = $state['Destination']['entityid'];

        if (   in_array($remote_entity_sp, $this->_blocked, true) 
            || in_array($remote_entity_idp, $this->_blocked, true)
        ) {
            // User interaction nessesary. Throw exception on isPassive request 
            if (isset($state['isPassive']) && $state['isPassive'] == TRUE) {
                throw new SimpleSAML_Error_NoPassive('Unable to show blocked access page on passive request.');
            }

            // IdP or SP should be blocked. Save the state and redirect	
            $id = SimpleSAML_Auth_State::saveState($state, 'janus:accessblock');
            $url = SimpleSAML_Module::getModuleURL('janus/showaccessblock.php');
            SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
        }
    }
}
?>
