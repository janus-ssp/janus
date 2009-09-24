<?php
/*
 * JANUS
 */

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
	SimpleSAML_Auth_Default::initLogin(
		$authsource, 
		SimpleSAML_Utilities::selfURL(), 
		NULL, 
		array(
			'SPMetadata' =>	array(
				'token' => $_REQUEST['token'],
				'mail' => $_REQUEST['mail']
			)
		)
	);
}

$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setEmail($userid);

if(!$user->load(sspmod_janus_User::EMAIL_LOAD)) {
	SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/newUser.php'), array('mail' => $userid));
} else {
	if(isset($_GET['truncate'])) {
		$ucontrol = new sspmod_janus_UserController($janus_config);
		$ucontrol->truncateDB();
	} 
	SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/dashboard.php'));
}
?>
