<?php
class sspmod_janus_Auth_Process_AccessBlocker extends SimpleSAML_Auth_ProcessingFilter {
	
	private $blocked = array();
	
	public function __construct($config, $reserved) {
		assert('is_array($config)');
		
		// Call parent constructor
		parent::__construct($config, $reserved);

		// Process config aray
		foreach($config AS $name => $value) {
			if(!is_string($name)) {
				throw new Exception('Invalid config parameter: ' . var_export($name, TRUE));
			}

			// If parameter is blocked
			if($name === 'blocked') {
				if(!is_array($value) && is_string($value)) {
					$this->blocked = array($value);
				} else {
					$this->blocked = $value;
				}
			}
				
		}
	}

	public function process(&$state) {
		assert('is_array($state)');

		$session = SimpleSAML_Session::getInstance();

		// Where you are logged in is in the session
		$remote_entity_idp = $session->getIdP();
		// Where you are going is in the sessoon
		$remote_entity_sp = $state['Destination']['entityid'];
	
		if(in_array($remote_entity_sp, $this->blocked, TRUE) || in_array($remote_entity_idp, $this->blocked, TRUE)) {
			// Svae the state	
			$id = SimpleSAML_Auth_State::saveState($state, 'janus:accessblock');
			$url = SimpleSAML_Module::getModuleURL('janus/showaccessblock.php');
			SimpleSAML_Utilities::redirect($url, array('StateId' => $id));
		}
	}
}
?>
