<?php
/*
 * JANUS
 */


$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

if (!$session->isValid('janus') ) {
	SimpleSAML_Utilities::redirect(
	   SimpleSAML_Module::getModuleURL('janus/janus-login.php'),
	   array('RelayState' => SimpleSAML_Utilities::selfURL())
	);
}

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
