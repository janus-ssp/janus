<?php
/*
 * JANUS
 */


$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$ucontrol = new sspmod_janus_UserController($janus_config);

$users = $ucontrol->getUsers();

if(isset($_GET['truncate'])) {
	$ucontrol->truncateDB();
	echo "All entities has been deleted from the DB.<br /><br />";
} 


$et = new SimpleSAML_XHTML_Template($config, 'janus:janus-index.php', 'janus:janus');
$et->data['users'] = $users;
$et->show();
?>
