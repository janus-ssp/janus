<?php

$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$mcontroller = new sspmod_janus_EntityController($janus_config);

if(isset($_POST['submit'])) {
	$entityid = $_POST['entityid'];
} else {
	$entityid = $_GET['entityid'];
}

$revisionid = -1;
if(isset($_GET['revisionid'])) {
	$revisionid = $_GET['revisionid'];
}

if($revisionid > -1) {
	if(!$entity = $mcontroller->setEntity($entityid, $revisionid)) {
		die('Error in setEntity');
	}
} else {
	if(!$entity = &$mcontroller->setEntity($entityid)) {
		die('Error in setEntity');
	}
}
$mcontroller->loadEntity();


$update = FALSE;

if(isset($_POST['esubmit'])) {
	$entity->setSystem($_POST['entity_system']);
	$entity->setState($_POST['entity_state']);
	$entity->setType($_POST['entity_type']);

	$mcontroller->saveEntity();
}else if(isset($_POST['submit'])) {
	if(!empty($_POST['meta_key'])) {
		if($mcontroller->addMetadata($_POST['meta_key'], $_POST['meta_value'])) {
			$update = TRUE;
		}
	}
	if(!empty($_POST['att_key'])) {
		if($mcontroller->addAttribute($_POST['att_key'], $_POST['att_value'])) {
			$update = TRUE;
		}
	}
	if(!empty($_POST['meta_xml'])) {
		//echo "Import type: " . $entity->getType() . "<br>";
		if($entity->getType() == 'sp') {
			if($mcontroller->importMetadata20SP($_POST['meta_xml'])) {
				$update = TRUE;
			}
		} else if($entity->getType() == 'idp') {
			if($mcontroller->importMetadata20IdP($_POST['meta_xml'])) {
				$update = TRUE;
			}
		} else {
			die('Type error');
		}
	}
	if($update) {
		echo "Update";
		$mcontroller->saveEntity();
	}
} elseif(isset($_POST['musubmit'])) {

	foreach($_POST AS $key => $value) {
		if(!empty($value) && !is_array($value)) {
			if($mcontroller->updateMetadata($key, $value)) {
				$update = TRUE;
			}
		}
	}

	if(isset($_POST['delete'])) {
		foreach($_POST['delete'] AS $data) {
			if($mcontroller->removeMetadata($data)) {
				$update = TRUE;
			}
		}
	}

	if($update) {
		$mcontroller->saveEntity();
		echo "Metadata update.";
	}
} elseif(isset($_POST['ausubmit'])) {
	foreach($_POST AS $key => $value) {
		if(!empty($value) && !is_array($value)) {
			if($mcontroller->updateAttribute($key, $value)) {
				$update = TRUE;
			}
		}
	}
	if(isset($_POST['delete'])) {
		foreach($_POST['delete'] AS $data) {
			if($mcontroller->removeAttribute($data)) {
				$update = TRUE;
			}
		}
	}
	if($update) {
		$mcontroller->saveEntity();
		echo "Metadata update.";
	}
}



// Display stuff

$systems = $janus_config->getValue('systems');
$states = $janus_config->getValue('states');
$types = $janus_config->getValue('types');
?>
<h2>Entity</h2>

<form method="post" action="">
	<table>
		<tr>
			<td>Entityid:</td>
			<td><?php echo $entity->getEntityid(); ?></td>
		</tr>
		<tr>
			<td>System:</td>
			<td>
				<select name="entity_system">
				<?php
				foreach($systems AS $system) {
					if($entity->getSystem() == $system) {
						echo '<option value="'. $system .'" selected="selected">'. $system .'</option>';
					} else {
						echo '<option value="'. $system .'">'. $system .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td>State:</td>
			<td>
				<select name="entity_state">
				<?php
				foreach($states AS $state) {
					if($entity->getState() == $state) {
						echo '<option value="'. $state .'" selected="selected">'. $state .'</option>';
					} else {
						echo '<option value="'. $state .'">'. $state .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td>Type:</td>
			<td>
				<select name="entity_type">
				<?php
				foreach($types AS $type) {
					if($entity->getType() == $type) {
						echo '<option value="'. $type .'" selected="selected">'. $type .'</option>';
					} else {
						echo '<option value="'. $type .'">'. $type .'</option>';
					}
				}
				?>
				</select>		
			</td>
		</tr>
		<tr>
			<td colspan="2"><input name="esubmit" type="submit" value="Update"></td>
		</tr>
	</table>
</form>



<?php





echo "<h2>History</h2>";
if(!$history = $mcontroller->getHistory()) {
	echo "Not history fo entity ". $entityid. '<br /><br />';
} else {
	foreach($history AS $data) {
		echo '<a href="?entityid='. $data->getEntityid() .'&revisionid='. $data->getRevisionid().'">'. $data->getEntityid() .' - '. $data->getRevisionid() .'</a><br>';
	}
}

echo "<br><hr><h2>Metadata</h2>";
if(!$metadata = $mcontroller->getMetadata()) {
	echo "Not metadata for entity ". $_GET['entityid']. '<br /><br />';
} else {
	echo '<form method="post" action="">';
	foreach($metadata AS $data) {

		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete[]"><br>';
	}
	echo '<input type="submit" name="musubmit" value="Update">';
	echo '</form>';
}

echo "<br><hr><h2>Attributes</h2>";

if(!$attributes = $mcontroller->getAttributes()) {
	echo "Not attributes for entity ". $_GET['entityid']. '<br /><br />';
} else {
	echo '<form method="post" action="">';
	foreach($attributes AS $data) {

		echo $data->getEntityid() .' - '. $data->getRevisionid().' - ' . $data->getkey() . ' - '. $data->getValue() .'<input type="text" name="'. $data->getKey()  .'"><input type="checkbox" value="'. $data->getKey() .'" name="delete[]"><br>';
	}
	echo '<input type="submit" name="ausubmit" value="Update">';
	echo '</form>';
}
?>
<br><hr>
<form method="post" action="">
	<input type="hidden" name="entityid" value="<?php echo $entityid; ?>">
	<table>
		<tr>
			<td colspan="2">Metadata:</td>
		</tr>
		<tr>
			<td>Key:</td>
			<td><input type="text" name="meta_key"></td>
		</tr>
		<tr>
			<td>Value:</td>
			<td><input type="text" name="meta_value"></td>
		</tr>
		<tr>
			<td>XML:</td>
			<td><textarea name="meta_xml" cols="80" rows="20"></textarea></td>
		</tr>
		<tr>
			<td colspan="2">Attribute:</td>
		</tr>
		<tr>
			<td>Key:</td>
			<td><input type="text" name="att_key"></td>
		</tr>
		<tr>
			<td>Value:</td>
			<td><input type="text" name="att_value"></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit" value="Create"></td>
		</tr>
	</table>
</form>
<?php
if($entity->getType() == 'idp') {
	echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/idp-metadata.php') .'?entityid='. $entity->getEntityid()  .'&revisionid='. $entity->getRevisionid() .'&output=xhtml">Export Metadata</a><br /><br />';
}
echo '<a href="'. SimpleSAML_Module::getModuleURL('janus/index.php') .'">Frontpage</a><br /><br />';
?>
