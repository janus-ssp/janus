<?php

$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$econtroller = new sspmod_janus_EntityController($janus_config);

if(isset($_POST['submit'])) {
	$userid = $_POST['userid'];
} else {
	$userid = $_GET['id'];
}

if(!$econtroller->setUser($userid)) {
	die('Error in setUser');
}

if(isset($_POST['submit'])) {
	$econtroller->createNewEntity($_POST['entityid']);
}



if(!$entities = $econtroller->getEntities()) {
	echo "Not entities for user ". $_GET['id']. '<br /><br />';
} else {
	foreach($entities AS $entity) {
		echo $entity['created'] . ' - ' . $entity['entityid'] . '<br>';
	}
}
?>
<form method="post" action="">
	<input type="hidden" name="userid" value="<?php echo $userid; ?>">
	Entityid: <input type="text" name="entityid"><br/>
	<input type="submit" name="submit" value="Create"><br/>
</form>



<?php

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>
