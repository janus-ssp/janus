<?php
//$session = SimpleSAML_Session::getInstance();
//$config = SimpleSAML_Configuration::getConfig('module_janus.php');
$config = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($config, 'janus:install.php', 'janus:janus');
$t->data['header'] = 'JANUS - Install';

if(isset($_POST['action']) && $_POST['action'] == 'install') {

	// Get DB connection info
	//$store = $config->getValue('store');

	$type = $_POST['dbtype'];	
	$host = $_POST['dbhost'];
	$name = $_POST['dbname'];
	$prefix = $_POST['dbprefix'];
	$user = $_POST['dbuser'];
	$pass = $_POST['dbpass'];

	$dsn = $type .':host='. $host . ';dbname='. $name;

	try {
		$admin_email = $_POST['admin_email']; 
		$admin_name = $_POST['admin_name']; 

		$dbh = new PDO($dsn, $user, $pass);

		$dbh->beginTransaction();

		// Token table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."tokens`;");
		$dbh->exec("CREATE TABLE `". $prefix ."tokens` (
				`id` int(11) NOT NULL auto_increment,
				`mail` varchar(320) NOT NULL,
				`token` varchar(255) NOT NULL,
				`notvalidafter` varchar(255) NOT NULL,
				`usedat` varchar(255) default NULL,
				 PRIMARY KEY  (`id`),
				 UNIQUE KEY `token` (`token`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// User table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."user`;");
		$dbh->exec("CREATE TABLE `". $prefix ."user` (
			`uid` int(11) NOT NULL auto_increment,
			`type` text,
			`email` varchar(320) default NULL,
			`active` char(3) default 'yes',
			`update` char(25) default NULL,
			`created` char(25) default NULL,
			`ip` char(15) default NULL,
			`data` text,
			PRIMARY KEY  (`uid`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Insert admin user
		$st = $dbh->prepare("INSERT INTO `". $prefix ."user` (`uid`, `type`, `email`, `active`, `update`, `created`, `ip`, `data`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
		$st->execute(array(NULL, 'admin', $admin_email, 'yes', date('c'), date('c'), $_SERVER['REMOTE_ADDR'], 'Navn: '.$admin_name));

		//i UserData table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."userData`;");
		$dbh->exec("CREATE TABLE `". $prefix ."userData` (
			`uid` int(11) NOT NULL,
			`key` varchar(255) NOT NULL,
			`value` varchar(255) NOT NULL,
			`update` char(25) NOT NULL,
			`created` char(25) NOT NULL,
			`ip` char(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Entity table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."entity`;");
		$dbh->exec("CREATE TABLE `". $prefix ."entity` (
            `eid` int(11) NOT NULL,
            `entityid` text NOT NULL,
            `revisionid` int(11) default NULL,
            `state` text,
            `type` text,
            `expiration` char(25) default NULL,
            `metadataurl` text,
            `allowedall` char(3) NOT NULL default 'yes',
            `created` char(25) default NULL,
            `ip` char(15) default NULL,
            `parent` int(11) default NULL,
            `revisionnote` text
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
        ");
		// Metadata table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."metadata`;");
		$dbh->exec("CREATE TABLE `". $prefix ."metadata` (
			`eid` int(11) NOT NULL,
			`revisionid` int(11) NOT NULL,
			`key` text NOT NULL,
			`value` text NOT NULL,
			`created` char(25) NOT NULL,
			`ip` char(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Attribute table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."attribute`;");
		$dbh->exec("CREATE TABLE `". $prefix ."attribute` (
			`entityid` text NOT NULL,
			`revisionid` int(11) NOT NULL,
			`key` text NOT NULL,
			`value` text NOT NULL,
			`created` char(25) NOT NULL,
			`ip` char(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Blocked entities table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."blockedEntity`;");
		$dbh->exec("CREATE TABLE `". $prefix ."blockedEntity` (
			`entityid` text NOT NULL,
			`revisionid` int(11) NOT NULL,
			`remoteentityid` text NOT NULL,
			`created` char(25) NOT NULL,
			`ip` char(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Relation between user and entity table
		$dbh->exec("DROP TABLE IF EXISTS `". $prefix ."hasEntity`;");
		$dbh->exec("CREATE TABLE `". $prefix ."hasEntity` (
			`uid` int(11) NOT NULL,
			`entityid` text,
			`created` char(25) default NULL,
			`ip` char(15) default NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

		// Commit all sql
		$success = $dbh->commit();
        } catch(Exception $e) {
            $t->data['success'] = FALSE;
            $t->show();
        }

		if($success) {
			include('config_template.php');
			$config_template['store']['dsn'] = $dsn;
			$config_template['store']['username'] = $user;
			$config_template['store']['password'] = $pass;
			$config_template['store']['prefix'] = $prefix;
			$config_template['admin.name'] = $admin_name;
			$config_template['admin.email'] = $admin_email;
        
            $t->data['success'] = $success;     
            $t->data['config_template'] = $config_template; 
		    $t->data['prefix'] = $prefix;	
		    $t->data['email'] = $admin_email;
		    $t->data['dsn'] = $dsn;
		    $t->data['user'] = $user;
		    $t->data['pass'] = $pass;
        } else {
            $t->data['success'] = FALSE;
        }
    }
$t->show();
?>
