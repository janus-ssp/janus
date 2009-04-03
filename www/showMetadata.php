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

$update = FALSE;
if(isset($_POST['submit'])) {
	if(!empty($_POST['meta_key'])) {
		$mcontroller->createNewMetadata($_POST['meta_key'], $_POST['meta_value']);
		$update = TRUE;
	}
	if(!empty($_POST['att_key'])) {
		$mcontroller->createNewAttribute($_POST['att_key'], $_POST['att_value']);
		$update = TRUE;
	}
	if($update) {
		echo "Update";
		$mcontroller->saveEntity();
	}
}


echo "<h2>Metadata</h2>";
if(!$metadata = $mcontroller->getMetadata()) {
	echo "Not metadata fo entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($metadata AS $data) {

		echo $data->getEntityid() .' - '. $data->getrevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<br>';
	}
}

echo "<br><hr><h2>Attributes</h2>";

if(!$attributes = $mcontroller->getAttributes()) {
	echo "No attributes fo entity ". $_GET['entityid']. '<br /><br />';
} else {
	foreach($attributes AS $data) {

		echo $data->getEntityid() .' - '. $data->getrevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<br>';
	}
}
?>
<br><hr>
<form method="post" action="">
	Metadata:<br>
	<input type="hidden" name="entityid" value="<?php echo $entityid; ?>">
	Key: <input type="text" name="meta_key"><br/>
	Value: <input type="text" name="meta_value"><br/>
	Attribute:<br>
	Key: <input type="text" name="att_key"><br/>
	Value: <input type="text" name="att_value"><br/>
	<input type="submit" name="submit" value="Create"><br/>
</form>
<?php

echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>
