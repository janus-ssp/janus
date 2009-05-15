<?php

$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

if (!$session->isValid('janus') ) {
	SimpleSAML_Utilities::redirect(
	   SimpleSAML_Module::getModuleURL('janus/janus-login.php'),
	   array('RelayState' => SimpleSAML_Utilities::selfURL())
  );
}

$mcontrol = new sspmod_janus_UserController($janus_config);

if(isset($_POST['submit'])) {
	$userid = $_POST['userid'];
} else {
	$userid = $_GET['id'];
}

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
