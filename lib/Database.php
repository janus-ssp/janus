<?php
abstract class sspmod_janus_Database {

	private $dsn;
	private $username;
	private $password;
	protected $prefix;
	private $db;

	protected function __construct(&$config) {
		assert('is_array($config)');

		foreach(array('dsn', 'username', 'password') as $id) {
			if (!array_key_exists($id, $config)) {
				throw new Exception('JANUS:Database - Missing required option \'' . $id . '\'.');
			}
			if (!is_string($config[$id])) {
				throw new Exception('JANUS:Database - \'' . $id . '\' is supposed to be a string.');
			}

			$this->dsn = $config['dsn'];
			$this->username = $config['username'];
			$this->password = $config['password'];
			$this->prefix = $config['prefix'];

			$db = $this->getDB();
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
	 */
	protected function execute($statement, $parameters) {
		assert('is_string($statement)');
		assert('is_array($parameters)');

		$db = $this->getDB();
		if ($db === FALSE) {
			return FALSE;
		}

		$st = $db->prepare($statement);
		if ($st === FALSE) {
			if ($st === FALSE) {
				SimpleSAML_Logger::error('JANUS:Database - Error preparing statement \'' .
					$statement . '\': ' . self::formatError($db->errorInfo()));
				return FALSE;
			}
		}

		if ($st->execute($parameters) !== TRUE) {
			SimpleSAML_Logger::error('JANUS:Database - Error executing statement \'' .
				$statement . '\': ' . self::formatError($st->errorInfo()));
			return FALSE;
		}

		return $st;
	}
	
	/**
	 * Format PDO error.
	 *
	 * This function formats a PDO error, as returned from errorInfo.
	 *
	 * @param array $error  The error information.
	 * @return string  Error text.
	 */
	private static function formatError($error) {
		assert('is_array($error)');
		assert('count($error) >= 3');

		return $error[0] . ' - ' . $error[2] . ' (' . $error[1] . ')';
	}

	/**
	 * Get database handle.
	 *
	 * @return PDO|FALSE  Database handle, or FALSE if we fail to connect.
	 */
	private function getDB() {
		if ($this->db !== NULL) {
			return $this->db;
		}

		try {
			$this->db = new PDO($this->dsn, $this->username, $this->password);
		} catch (PDOException $e) {
			SimpleSAML_Logger::error('janus:Database - Failed to connect to \'' .	$this->dsn . '\': '. $e->getMessage());
			$this->db = FALSE;
		}

		return $this->db;
	}

	abstract public function save();
	abstract public function load();
	abstract public function get();
	abstract public function set();


}
?>
