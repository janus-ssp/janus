<?php
/*
 * JANUS
 */

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/newUser.php') .'">New User</a><br /><br />';

$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$ucontrol = new sspmod_janus_UserController($janus_config);

$users = $ucontrol->getUsers();

if(isset($_GET['truncate'])) {
	$ucontrol->truncateDB();
	echo "All entities has been deleted from the DB.<br /><br />";
} 




//$econtroller = new sspmod_janus_EntityController($janus_config);

//$users = $econtroller->getUsers();

foreach($users AS $user) {
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/showEntities.php') .'?id='. $user['email'] .'">'. $user['email'] .'</a><br />';
}
?>
