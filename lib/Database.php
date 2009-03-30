<?php
/**
 * Contains a basic class for handling storage in JANUS.
 *
 * @author Jacob Christiansen <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Abstarct class for implementing storage handling in JANUS.
 *
 * The class implements a constructor that parses the configuration and basic
 * methods for using the database.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
abstract class sspmod_janus_Database {

	/**
	 * DSN for the database.
	 * @var string
	 */
	private static $dsn;
	
	/**
	 * Username for the database.
	 * @var string
	 */	
	private static $username;
	
	/**
	 * Password for the database.
	 * @var string
	 */
	private static $password;
	
	/**
	 * Database handler. Can not be serialized.
	 * @var PDO
	 * @see PHP_MANUAL#class.pdo
	 */
	protected static $db = NULL;

	/**
	 * Prefix for the tables in the database.
	 * @var string
	 */
	protected static $prefix;

	/**
	 * __construct
	 *
	 * Class constructor that parses the configuration. If the configuration is
	 * invalid a exception will be throwen.
	 *
	 * @param array &$config Configuration for database.
	 * @throws Exception 
	 */
	protected function __construct(&$config) {
		assert('is_array($config)');

		foreach(array('dsn', 'username', 'password') as $id) {
			if (!array_key_exists($id, $config)) {
				throw new Exception('JANUS:Database - Missing required option \'' . $id . '\'.');
			}
			if (!is_string($config[$id])) {
				throw new Exception('JANUS:Database - \'' . $id . '\' is supposed to be a string.');
			}

			self::$dsn = $config['dsn'];
			self::$username = $config['username'];
			self::$password = $config['password'];
			self::$prefix = $config['prefix'];
		}
	}

	/**
	 * Prepare and execute statement.
	 *
	 * This function prepares and executes a statement. On error, FALSE will be returned.
	 *
	 * @param string $statement  The statement which should be executed.
	 * @param array $parameters  Parameters for the statement.
	 * @return PDOStatement|FALSE  The statement, or FALSE if execution failed.
	 * @see PHP_MANUAL#PDOStatement
	 */
	protected function execute($statement, $parameters) {
		assert('is_string($statement)');
		assert('is_array($parameters)');

		$db = $this->getDB();
		if ($db === NULL) {
			return FALSE;
		}
		try {
			$st = $db->prepare($statement);
		} catch(PDOException $e) {
			SimpleSAML_Logger::error('JANUS:Database - Error preparing statement \'' . $statement . '\': '. self::formatError($db->errorInfo()));
			return FALSE;
		}
		if ($st->execute($parameters) !== TRUE) {
			SimpleSAML_Logger::error('JANUS:Database - Error executing statement \'' . $statement . '\': ' . self::formatError($st->errorInfo()));
			return FALSE;
		}
		return $st;
	}

	/**
	 * Format PDO error.
	 *
	 * This function formats a PDO error, as returned from errorInfo.
	 *
	 * @param array $error The error information.
	 * @return string Error text.
	 * @see PHP_MANUAL#PDO.errorInfo
	 */
	protected static function formatError($error) {
		assert('is_array($error)');
		assert('count($error) >= 3');

		return $error[0] . ' - ' . $error[2] . ' (' . $error[1] . ')';
	}

	/**
	 * Get database handle.
	 *
	 * @return PDO|NULL Database handle, or NULL if we fail to connect
	 * @see PHP_MANUAL#class.pdo
	 */
	private static function getDB() {
		if (self::$db !== NULL) {
			return self::$db;
		}

		try {
			self::$db = new PDO(self::$dsn, self::$username, self::$password);

			/*
			 * Set the error reporting attribute
			 */
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		} catch (PDOException $e) {
			SimpleSAML_Logger::error('janus:Database - Failed to connect to \'' .	self::$dsn . '\': '. $e->getMessage());
			self::$db = NULL;
		}
		
		return self::$db;
	}
}
?>
