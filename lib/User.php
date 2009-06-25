<?php
/**
 * Contains User class for JANUS.
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package simpleSAMLphp
 * @subpackage JANUS
 * @version $Id$
 */
/**
 * Class implementing a JANUS user.
 *
 * User class that extends the Database class implementing basic functionality
 * used for user generation and updating.
 *
 * @package simpleSAMLphp
 * @subpackage JANUS
 */
class sspmod_janus_User extends sspmod_janus_Database {

	/**
	 * Constant telling load() to load the user using the uid.
	 */
	const UID_LOAD = '__LOAD_WITH_UID__';
	
	/**
	 * Constant telling load() to load the user using the email.
	 */
	const EMAIL_LOAD = '__LOAD_WITH_EMAIL__';

	/**
	 * Users uid
	 * @var integer
	 */
	private $_uid;
	
	/**
	 * User email
	 * @var string
	 */
	private $_email;
	
	/**
	 * User type
	 * @var string
	 */
	private $_type;

	/**
	 * User data.
	 * @var array
	 */
	private $_data;

	/**
	 * Indicates whether the user data has been modified.
	 * @var bool
	 */
	private $_modified = FALSE;

	/**
	 * Class constructor.
	 *
	 * Class constructor that parses the configuration and initialize the user
	 * object.
	 *
	 * @param array $config Configuration for the database
	 */
	public function __construct($config) {
		// To start with only the store config is parsed til user
		parent::__construct($config);
	}

	/**
	 * Saves the user data to the database.
	 *
	 * Method for saving the user data to the database. If the user data has not
	 * been modified the methos just returns TRUE. If an error occures and the
	 * data is not saved the method returns FALSE.
	 *
	 * @return bool TRUE if data is saved end FALSE if data is not saved.
	 * @todo Fix
	 * 	- Clean up
	 * 	- Remove exceptions, return TRUE/FALSE	
	 */
	public function save() {
		// If the object is not modified, don't save it.
		if(!$this->_modified) {
			return TRUE;
		}
				
		if(empty($this->_uid)) {
			// Test if email address already exists
			$st = $this->execute('SELECT count(*) AS `count` FROM '. self::$prefix .'__user WHERE `email` = ?;', array($this->_email));
			if($st === FALSE) {
				throw new Exception('JANUS:User:save - Error executing statement \'' . $statement . '\': ' . self::formatError($st->errorInfo()));
			}

			$row = $st->fetchAll(PDO::FETCH_ASSOC);
			if($row[0]['count'] > 0) {
				throw new Exception('JANUS:User:save: Email already exists. Can not create new User.');
			}

			// Create new User
			$statement = 'INSERT INTO '. self::$prefix .'__user (`uid`, `type`, `email`, `update`, `created`, `ip`) VALUES (NULL, ?, ?, ?, ?, ?)';
			$st = $this->execute(
				$statement,
				array($this->_type, $this->_email, date('c'), date('c'), $_SERVER['REMOTE_ADDR'])
			);

			// Get new uid
			$this->_uid = self::$db->lastInsertId();
		} else {
			$statement = 'UPDATE '. self::$prefix .'__user set `type` = ?, `email` = ?, `update` = ?, `ip` = ?, `data` = ? WHERE `uid` = ?;';
			$st = $this->execute(
				$statement,
				array($this->_type, $this->_email, date('c'), $_SERVER['REMOTE_ADDR'], $this->_data, $this->_uid)
			);
		}

		if($st === FALSE) {
			throw new Exception('JANUS:User:save - Error executing statement \'' . $statement . '\': ' . self::$db->errorInfo());
		}

		$this->_modified = FALSE;

		return TRUE;
	}

	/**
	 * Load user data from database.
	 *
	 * The methos loades the user data from the database, either by uid or by
	 * email. Which depends on the flag parsed to the method. hest 
	 *
	 * @param const $flag Flag to indicate load method.
	 * @return PDOStatement|bool The statement or FALSE if an error has occured.
	 * @todo Fix 
	 * 	- Skal kun returnere TRUE/FALSE (fjern exceptions)
	 * 	- Proper validation of $st
	 */
	public function load($flag = UID_LOAD) {

		if($flag === self::UID_LOAD) {
			// Load user using uid
			$st = $this->execute('SELECT * FROM '. self::$prefix .'__user WHERE `uid` = ?', array($this->_uid));
		} else if($flag === self::EMAIL_LOAD) {	
			// Load user using email
			$st = $this->execute('SELECT * FROM '. self::$prefix .'__user WHERE `email` = ?', array($this->_email));
		} else {
			throw new Exception('JANUS:User:load: Invalid flag parsed - '. var_export($flag));
		}

		if($st === FALSE) {
			throw new Exception('JANUS:User:save - Error executing statement \'' . $statement . '\': ' . self::$db->errorInfo());
		}

		$rs = $st->fetchAll(PDO::FETCH_ASSOC);

		if($row = $rs[0]) {
			$this->_uid = $row['uid'];
			$this->_email = $row['email'];
			$this->_type = $row['type'];
			$this->_data = $row['data'];

			$this->_modified = FALSE;
		} else {
			return FALSE;
		}

		return $st;
	}
	
	/**
	 * Set user id.
	 *
	 * Method to set the user id. Method sets _modified to TRUE.
	 *
	 * @param int $uid User id
	 */
	public function setUid($uid) {
		assert('ctype_digit($uid)');

		$this->_uid = $uid;
	
		$this->_modified = TRUE;
	}
	
	/**
	 * Det user email.
	 *
	 * Method for setting the user email. The method does not validate the
	 * correctness of the email, only that it is a string and that is is not
	 * longer that 320 chars. Method sets _modified to TRUE.
	 *
	 * @param string $email User email.
	 * @todo Validate email. 
	 */ 
	public function setEmail($email) {
		assert('is_string($email)');
		assert('strlen($email) <= 320');
		
		$this->_email = $email;

		$this->_modified = TRUE;
	}

	/**
	 * Set user type.
	 *
	 * Method for setting the user type. Method sets _modified to TRUE.
	 *
	 * @param string $type User type.
	 * @todo Test that type is valid according to the config.
	 */
	public function setType($type) {
		assert('is_string($type)');

		$this->_type = $type;

		$this->_modified = TRUE;
	}

	/**
	 * Get user id.
	 *
	 * Method for getting the user id.
	 *
	 * @return int The user id.
	 */
	public function getUid() {
		return $this->_uid;
	}

	/**
	 * Get user email.
	 *
	 * Method for getting the user email.
	 *
	 * @return string The user email.
	 */
	public function getEmail() {
		return $this->_email;
	}

	/**
	 * Get user type.
	 *
	 * Method for getting the user type.
	 *
	 * @return string The user type.
	 */
	public function getType() {
		return $this->_type;	
	}

	public function getData() {
		return $this->_data;
	}

	/**
	 * Get modified information.
	 *
	 * Method for getting the status of the _modified variable.
	 *
	 * @return bool TRUE in user data is modified.
	 */
	public function isModified() {
		return $this->_modified;
	}
	
	public function setData($data) {
		assert('is_string($data)');

		$this->_data = $data;

		$this->_modified = TRUE;
	}

}
?>
