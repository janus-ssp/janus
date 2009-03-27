<?php
/*
 * JANUS
 */

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/newUser.php') .'">New User</a><br /><br />';

$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');
$econtroller = new sspmod_janus_EntityController($janus_config);

$users = $econtroller->getUsers();

foreach($users AS $user) {
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/showEntities.php') .'?id='. $user['email'] .'">'. $user['email'] .'</a><br />';
}
?>
