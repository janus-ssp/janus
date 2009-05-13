<?php
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');
$econtroller = new sspmod_janus_UserController($janus_config);

$usertypes = $janus_config->getValue('usertypes');

$et = new SimpleSAML_XHTML_Template($config, 'janus:janus-newUser.php', 'janus:janus');

if(isset($_POST['submit'])) {
	$user = new sspmod_janus_User($janus_config->getValue('store'));
	$user->setEmail($_POST['email']);
	$user->setType($_POST['type']);
	$user->save();
	$et->data['user_status'] = "New user created<br />";
}

$et->data['users'] = $econtroller->getUsers();
$et->data['usertypes'] = $usertypes;
$et->show();
?>

