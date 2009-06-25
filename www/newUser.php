<?php
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');


if ($session->isValid($authsource)) {
	$attributes = $session->getAttributes();
	// Check if userid exists
	if (!isset($attributes[$useridattr])) 
		throw new Exception('User ID is missing');
	$userid = $attributes[$useridattr][0];
} else {
	SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$econtroller = new sspmod_janus_UserController($janus_config);

$usertypes = $janus_config->getValue('usertypes');

$et = new SimpleSAML_XHTML_Template($config, 'janus:janus-newUser.php', 'janus:janus');

if(isset($_POST['submit'])) {
	$user = new sspmod_janus_User($janus_config->getValue('store'));
	$user->setEmail($_POST['email']);
	$user->setType($_POST['type']);
	$user->save();
	$et->data['user_created'] = TRUE ;
}

if(isset($_GET['mail'])) {
	$et->data['mail'] = $_GET['mail'];
}
$et->data['users'] = $econtroller->getUsers();
$et->data['usertypes'] = $usertypes;
$et->show();
?>

