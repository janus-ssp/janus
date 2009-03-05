<?php

class sspmod_janus_User extends sspmod_janus_Database {

	private $uid;
	private $entityID;
	private $type;
	private $name;

	public function __construct($config) {
		// To start with only the store config is parsed til user
		parent::__construct($config);
	}

	public function setUid($uid) {
		assert('is_int($uid)');
		$this->uid = $uid;
	}

	public function save() {}

	public function load() {
		
		$ret = array();
		
		$st = $this->execute('SELECT * FROM '. $this->prefix .'__user WHERE `uid` = ?', array($this->uid));

		if($st === FALSE) {
			return array();
		}

		while($row = $st->fetch(PDO::FETCH_ASSOC)) {
			$this->entityID = $row['entityID'];
			$this->type = $row['type'];
			$this->name = $row['name'];
		}
	}
	
	public function get() {}
	
	public function set() {}
}
?>
