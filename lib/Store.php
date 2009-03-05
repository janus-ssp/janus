<?php

/**
 * Base class for consent storage handlers.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
abstract class sspmod_janus_Store {

	/**
	 * Constructor for the base class.
	 *
	 * This constructor should always be called first in any class which implements
	 * this class.
	 *
	 * @param array &$config  The configuration for this storage handler..
	 */
	protected function __construct(&$config) {
		assert('is_array($config)');
	}

	/**
	 * Parse consent storage configuration.
	 *
	 * This function parses the configuration for a consent storage method. An exception
	 * will be thrown if configuration parsing fails.
	 *
	 * @param mixed $config  The configuration.
	 * @return sspmod_consent_Store  An object which implements of the sspmod_consent_Store class.
	 */
	public static function parseStoreConfig($config) {

		if (is_string($config)) {
			$config = array($config);
		}

		if (!is_array($config)) {
			throw new Exception('Invalid configuration for consent store option: ' .
				var_export($config, TRUE));
		}

		if (!array_key_exists(0, $config)) {
			throw new Exception('Consent store without name given.');
		}

		$className = SimpleSAML_Module::resolveClass($config[0], 'Store',
			'sspmod_janus_Store');

		unset($config[0]);
		return new $className($config);
	}

}
?>
