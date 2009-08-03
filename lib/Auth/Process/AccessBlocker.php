<?php
/**
 * AccessBlocker processing filter for simpleSAMLphp
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Filter to block access to entities..
 *
 * This filter is a basic implementation of an access blocking processing 
 * filter for simpleSAMLphp. The implementation is user in JANUS. The filter is 
 * configured in either the global config file or in the metadata files for the 
 * entities. 
 * <code>
 * 10 => array(
 * 	'class' => 'janus:AccessBlocker',
 * 		'blocked' => array(
 *			'entity1',
 *			'entity2',
 *			...
 * 		),
 * 	),
 * </code>
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_Auth_Process_AccessBlocker extends SimpleSAML_Auth_ProcessingFilter {
	
	/**
	 * Array of blocked entities.
	 * @var array
	 */
	private $blocked = array();
	
	/**
	 * Initialize this filter.
	 *
	 * @param array $config Configuration information about this filter.
	 * @param mixed $reserved For future use.
	 */
	public function __construct($config, $reserved) {
		assert('is_array($config)');
		
		// Call parent constructor
		parent::__construct($config, $reserved);

		// Process config array
		foreach($config AS $name => $value) {
			if(!is_string($name)) {
				throw new Exception('Invalid config parameter: ' . var_export($name, TRUE));
			}

			// If parameter is `blocked`
			if($name === 'blocked') {
				if(!is_array($value) && is_string($value)) {
					$this->blocked = array($value);
				} else {
					$this->blocked = $value;
				}
			}
		}
	}

	/**
	 * Apply filter to block entities.
	 *
	 * Block the entity making the request if it is set in the configuration.
	 *
	 * @param array &$state The current state.
	 */
	public function process(&$state) {
		assert('is_array($state)');

		$session = SimpleSAML_Session::getInstance();

		// Get the IdP
		$remote_entity_idp = $session->getIdP();
		// Get the SP
		$remote_entity_sp = $state['Destination']['entityid'];
	
		if(in_array($remote_entity_sp, $this->blocked, TRUE) || in_array($remote_entity_idp, $this->blocked, TRUE)) {
			// IdP or SP should be blocked. Save the state and redirect	
			$id = SimpleSAML_Auth_State::saveState($state, 'janus:accessblock');
			$url = SimpleSAML_Module::getModuleURL('janus/showaccessblock.php');
			SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
		}
	}
}
?>
