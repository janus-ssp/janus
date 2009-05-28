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


$mcontrol = new sspmod_janus_UserController($janus_config);

if(!$mcontrol->setUser($userid)) {
	die('Error in setUser');
}

if(isset($_POST['submit'])) {
	$mcontrol->createNewEntity($_POST['entityid']);

}

$et = new SimpleSAML_XHTML_Template($config, 'janus:janus-showEntities.php', 'janus:janus');
$et->data['entities'] = $mcontrol->getEntities();
$et->data['userid'] = $userid;
$et->show();

?>
