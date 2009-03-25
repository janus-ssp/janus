<?php

class sspmod_janus_User extends sspmod_janus_Database {

	const UID_LOAD = '__LOAD_WITH_UID__';
	const EMAIL_LOAD = '__LOAD_WITH_EMAIL__';

	private $_uid;
	private $_email;
	private $_type;

	private $_modified = FALSE;

	public function __construct($config, $uid = NULL) {
		// To start with only the store config is parsed til user
		parent::__construct($config);
		if($uid !== NULL) {
			assert('ctype_digit($uid)');
			$this->_uid = $uid;

			$this->_modified = TRUE;
		}
	}

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
			$statement = 'UPDATE '. self::$prefix .'__user set `type` = ?, `email` = ?, `update` = ?, `created` = ?, `ip` = ? WHERE `uid` = ?;';
			$st = $this->execute(
				$statement,
				array($this->_type, $this->_email, date('c'), date('c'), $_SERVER['REMOTE_ADDR'])
			);
		}

		if($st === FALSE) {
			throw new Exception('JANUS:User:save - Error executing statement \'' . $statement . '\': ' . self::$db->errorInfo());
		}

		$this->_modified = FALSE;

		return TRUE;
	}

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

		while($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->_uid = $row['uid'];
			$this->_email = $row['email'];
			$this->_type = $row['type'];

			$this->_modified = FALSE;
		}

		return TRUE;
	}

	public function setUid($uid) {
		assert('ctype_digit($uid)');

		$this->_uid = $uid;
	
		$this->_modified = TRUE;
	}

	public function setEmail($email) {
		assert('is_string($email)');
		assert('strlen($email) <= 320');
		
		$this->_email = $email;

		$this->_modified = TRUE;
	}

	public function setType($type) {
		assert('is_string($type)');

		$this->_type = $type;

		$this->_modified = TRUE;
	}
	
	public function getUid() {
		return $this->_uid;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function getType() {
		return $this->_type;	
	}

	public function isModified() {
		return $this->_modified;
	}
}
?>
