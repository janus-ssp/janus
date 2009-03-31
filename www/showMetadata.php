<?php

$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$mcontroller = new sspmod_janus_EntityController($janus_config);



if(isset($_POST['submit'])) {
	$entityid = $_POST['entityid'];
} else {
	$entityid = $_GET['entityid'];
}

if(!$mcontroller->setEntity($entityid)) {
	die('Error in setEntity');
}


if(isset($_POST['submit']) && !empty($_POST['keyname'])) {
	$mcontroller->createNewMetadata($_POST['keyname'], $_POST['value']);
	$mcontroller->saveEntity();
}


if(!$metadata = $mcontroller->getMetadata()) {
	echo "Not metadata fo entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($metadata AS $data) {

		echo $data->getEntityid() .' - '. $data->getrevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<br>';
	}
}
?>
<form method="post" action="">
	<input type="hidden" name="entityid" value="<?php echo $entityid; ?>">
	Key: <input type="text" name="keyname"><br/>
	Value: <input type="text" name="value"><br/>
	<input type="submit" name="submit" value="Create"><br/>
</form>
<?php

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>
