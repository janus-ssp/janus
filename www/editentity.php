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

$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
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

// Check if user is allowed to se entity
$allowedUsers = $mcontroller->getUsers();
if(!array_key_exists($userid, $allowedUsers)) {
	SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$update = FALSE;

if(!empty($_POST)) {
	// Metadata
	if(!empty($_POST['meta_key'])) {
		if($_POST['meta_key'] != 'NULL' && $mcontroller->addMetadata($_POST['meta_key'], $_POST['meta_value'])) {
			$update = TRUE;
		}
	}

	if(!empty($_POST['meta_xml'])) {
		if($entity->getType() == 'sp') {
			if($msg = $mcontroller->importMetadata20SP($_POST['meta_xml'])) {
				$update = TRUE;
			}
		} else if($entity->getType() == 'idp') {
			if($msg = $mcontroller->importMetadata20IdP($_POST['meta_xml'])) {
				$update = TRUE;
			}
		} else {
			die('Type error');
		}
	}

	// Update metadata and attributes
	foreach($_POST AS $key => $value) {
		if(substr($key, 0, 14) == 'edit-metadata-') {
			if(!empty($value) && !is_array($value)) {
				$newkey = substr($key, 14, strlen($key));
				if($mcontroller->updateMetadata($newkey, $value)) {
					$update = TRUE;
				}
			}
		} else if(substr($key, 0, 15) == 'edit-attribute-') {
			if(!empty($value) && !is_array($value)) {
				$newkey = substr($key, 15, strlen($key));
				if($mcontroller->updateAttribute($newkey, $value)) {
					$update = TRUE;
				}
			}
		}
	}
	
	if(isset($_POST['delete-metadata'])) {
		foreach($_POST['delete-metadata'] AS $data) {
			if($mcontroller->removeMetadata($data)) {
				$update = TRUE;
			}
		}
	}

	// Remote entities 	
	if(isset($_POST['add'])) {
		$mcontroller->setAllowedAll('yes');
		$mcontroller->setAllowedAll('no');
		foreach($_POST['add'] AS $key) {
			if($mcontroller->addBlockedEntity($key)) {
				$update = TRUE;
			}
		}
	} else {
		$mcontroller->setAllowedAll('yes');
		$mcontroller->setAllowedAll('no');
		$update = TRUE;
	}
	
	// Allowedall	
	if(isset($_POST['allowedall'])) {
		if($mcontroller->setAllowedAll('yes')) {
			$update = TRUE;
		}
	} else {
		if($mcontroller->setAllowedAll('no')) {
			$update = TRUE;
		}
	}
	
	// Update entity if updated
	if($update) {
		$mcontroller->saveEntity();
	}
}

$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:janus');

$remote_entities = $metadata->getList('saml20-sp-remote');
$et->data['metadata_select'] = $janus_config->getValue('metadatafields.idp');


$et->data['entity_system'] = $entity->getSystem();
$et->data['entity_state'] = $entity->getState();
$et->data['entity_type'] = $entity->getType();
$et->data['revisionid'] = $entity->getRevisionid();
$et->data['entity'] = $entity;
$et->data['mcontroller'] = $mcontroller;
$et->data['blocked_entities'] = $mcontroller->getBlockedEntities();
$et->data['remote_entities'] = $remote_entities; 


$et->data['header'] = 'JANUS';
if(isset($msg)) {
	$et->data['msg'] = $msg;
}

$et->show();
?>
