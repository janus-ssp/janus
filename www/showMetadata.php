<?php
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');

$mcontroller = new sspmod_janus_EntityController($janus_config);

if(!empty($_POST)) {
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
	if($entity->setSystem($_POST['entity_system'])) {
		$update = TRUE;
	}
	if($entity->setState($_POST['entity_state'])) {
		$update = TRUE;
	}
	if($entity->setType($_POST['entity_type'])) {
		$update = TRUE;
	}
	if($update) {
		$mcontroller->saveEntity();
	}
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

$et = new SimpleSAML_XHTML_Template($config, 'janus:janus-showMetadata.php', 'janus:janus');

$et->data['entity_system'] = $entity->getSystem();
$et->data['entity_state'] = $entity->getState();
$et->data['entity_type'] = $entity->getType();
$et->data['revisionid'] = $entity->getRevisionid();
$et->data['systems'] = $janus_config->getValue('systems');
$et->data['states'] = $janus_config->getValue('states');
$et->data['types'] = $janus_config->getValue('types');
$et->data['entity'] = $entity;
$et->data['mcontroller'] = $mcontroller;

$et->data['header'] = 'JANUS';
$et->show();
?>
