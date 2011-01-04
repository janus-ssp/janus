<?php
/**
 * An AccessBlocker processing filter
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage ProcessingFilter
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
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
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
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
    private $_blocked = null;
    
    /**
     * Array of allowed entities
     * @var array
     */
    private $_allowed = null;


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
            } else if($name === 'allowed') {
                if (!is_array($value) && is_string($value)) {
                    $this->_allowed = array($value);
                } else {
                    $this->_allowed = $value;
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
        $block = false;
        
        // Get the IdP
        $remote_entity_idp = $session->getIdP();
        // Get the SP
        $remote_entity_sp = $state['Destination']['entityid'];

        // Check if access is blocked
        if (!is_null($this->_blocked) 
            && (in_array($remote_entity_sp, $this->_blocked, true)
            || in_array($remote_entity_idp, $this->_blocked, true))
        ) {
            $block = true;
        }

        // Check if access is allowed
        if (!is_null($this->_allowed) 
            && (in_array($remote_entity_sp, $this->_allowed, true)
            || in_array($remote_entity_idp, $this->_allowed, true))
        ) {
            $block = false;
        } else {
            $block = true;
        }

        // If access is blocked
        if ($block) {
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
