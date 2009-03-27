<?php
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

if(isset($_POST['submit'])) {
	$user = new sspmod_janus_User($janus_config->getValue('store'));
	$user->setEmail($_POST['email']);
	$user->setType($_POST['type']);
	$user->save();
	echo "New user created<br />";
}
?>

<form method="post" action="">
	Type: <input type="text" name="type" /><br />
	E-mail: <input type="text" name="email" /><br />
	<input type="submit" name="submit" value="Create">
</form>

<br />
<?php
$econtroller = new sspmod_janus_EntityController($janus_config);
$users = $econtroller->getUsers();

foreach($users AS $user) {
	echo $user['uid'] .' - '. $user['type'] .' - '. $user['email'] .' - '. $user['update'] .' - '. $user['created'] .' - '. $user['ip'] .'<br />';
}




echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>
