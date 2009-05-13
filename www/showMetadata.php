<?php
$config = SimpleSAML_Configuration::getInstance();
$janus_config = $config->copyFromBase('janus', 'module_janus.php');
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

$update = FALSE;

if(isset($_POST['submit'])) {
	// Attribute
	if(isset($_POST['delete-attribute'])) {
		foreach($_POST['delete-attribute'] AS $data) {
			if($mcontroller->removeAttribute($data)) {
				$update = TRUE;
			}
		}
	}
	
	if(!empty($_POST['att_key'])) {
		if($mcontroller->addAttribute($_POST['att_key'], $_POST['att_value'])) {
			$update = TRUE;
		}
	}

	// Metadata
	if(isset($_POST['delete-metadata'])) {
		foreach($_POST['delete-metadata'] AS $data) {
			if($mcontroller->removeMetadata($data)) {
				$update = TRUE;
			}
		}
	}
	
	if(!empty($_POST['meta_key'])) {
		if($mcontroller->addMetadata($_POST['meta_key'], $_POST['meta_value'])) {
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
	
	// Remote entities 	
	if(isset($_POST['add'])) {
		foreach($_POST['add'] AS $key) {
			if($mcontroller->addBlockedEntity($key)) {
				$update = TRUE;
			}
		}
	}	
	if(isset($_POST['delete'])) {
		foreach($_POST['delete'] AS $key) {
			if($mcontroller->removeBlockedEntity($key)) {
				$update = TRUE;
			}
		}
	}	

	// Allowedall	
	if(isset($_POST['allowedall'])) {
		if($entity->setAllowedall('yes')) {
			$update = TRUE;
		}
	} else {
		if($entity->setAllowedall('no')) {
			$update = TRUE;
		}
	}

	// Entity status, type, system
	if($entity->setSystem($_POST['entity_system'])) {
		$update = TRUE;
	}
	if($entity->setState($_POST['entity_state'])) {
		$update = TRUE;
	}
	if($entity->setType($_POST['entity_type'])) {
		$update = TRUE;
	}
	
	// Update entity if updated
	if($update) {
		$mcontroller->saveEntity();
	}
}

if($entity->getType() == 'sp') {
	$remote_entities = $metadata->getList('saml20-idp-remote');
} else {
	$remote_entities = $metadata->getList('saml20-sp-remote');
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
$et->data['blocked_entities'] = $mcontroller->getBlockedEntities();
$et->data['remote_entities'] = $remote_entities; 

$et->data['header'] = 'JANUS';
$et->show();
?>
