<?php
/*
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author pitbulk
 */
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

		// Get the sql file content
		$path = realpath('module.php');
		$sql_path = str_replace('module.php','../modules/janus/docs/janus.sql',$path);

		$contents = file_get_contents($sql_path);

		// Replace the $prefix table name
		$contents = str_replace('janus__', $prefix, $contents);

		// Remove C style and inline comments
		$comment_patterns = array('/\/\*.*(\n)*.*(\*\/)?/', //C comments
		                         '/\s*--.*\n/', //inline comments start with --
	                                 '/\s*#.*\n/', //inline comments start with #
	                           );
		$contents = preg_replace($comment_patterns, "\n", $contents);

		//Retrieve sql statements
		$statements = explode(";\n", $contents);
		$statements = preg_replace("/\s/", ' ', $statements);

		foreach($statements as $statement) {
	                if($statement) {
		                $dbh->exec($statement.';');
		        }
		}


		// Insert admin user
		$st = $dbh->prepare("INSERT INTO `". $prefix ."user` (`uid`, `type`, `email`, `active`, `update`, `created`, `ip`, `data`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
		$st->execute(array(NULL, 'admin', $admin_email, 'yes', date('c'), date('c'), $_SERVER['REMOTE_ADDR'], 'Navn: '.$admin_name));

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
