<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */
// Initial import
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

// Get data from config
$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');
$workflow = $janus_config->getValue('workflow_states');

// Validate user
if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

function check_uri ($uri)
{
    if (preg_match('/^[a-z][a-z0-9+-\.]*:.+$/i', $uri) == 1) {
        return TRUE;
    }
    return FALSE;
}

// Get metadata to present remote entitites
$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
// Get Entity controller
$mcontroller = new sspmod_janus_EntityController($janus_config);

// Get the user
$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setUserid($userid);
$user->load(sspmod_janus_User::USERID_LOAD);

// Get Admin util which we use to retrieve entities
$autil = new sspmod_janus_AdminUtil();


// Function to fix up PHP's messing up POST input containing dots, etc.
function getRealPOST() {
    $vars = array();
    $input = file_get_contents("php://input");
    if(!empty($input)) {
        $pairs = explode("&", $input);
        foreach ($pairs as $pair) {
            $nv = explode("=", $pair);
            $name = urldecode($nv[0]);
            $value = urldecode($nv[1]);
            $name = explode('[', $name);
            if(count($name) > 1) {
                $subkey = substr($name[1], 0, -1);
                if(empty($subkey)) {
                    $vars[$name[0]][] = $value; 
                } else {
                    $vars[$name[0]][substr($name[1], 0, -1)] = $value;
                }
            } else {
                $vars[$name[0]] = $value;
            }
        }
    }
    return $vars;
}

// Fix the POST array. Metadata fields can contain . _ and more
$_POST = getRealPOST();

// Get correct revision
$revisionid = -1;
$msg = null;

// If post is set it has priority
if(!empty($_POST)) {
    if(!isset($_POST['eid']) | !isset($_POST['revisionid'])) {
        die('eid and revisionid parameter must be set');
    }
   $eid = $_POST['eid'];
   $revisionid = $_POST['revisionid']; 
} else if(!empty($_GET)) {
    if(!isset($_GET['eid'])) {
        die('eid parameter must be set');
    }
    $eid = $_GET['eid'];
    if(isset($_GET['revisionid'])) {
        $revisionid = $_GET['revisionid'];
    }
    if(isset($_GET['msg']) && !empty($_GET['msg'])) {
        $msg = $_GET['msg'];
    }
} else {
    die('eid and revisionid parameter must be set');
}

// Revisin id has been set. Fetch the correct version of the entity
if($revisionid > -1) {
    if(!$entity = $mcontroller->setEntity($eid, $revisionid)) {
        die('Error in setEntity');
    }
} else {
    // Revision not set, get latest
    if(!$entity = $mcontroller->setEntity($eid)) {
        die('Error in setEntity');
    }
}
// load entity
$mcontroller->loadEntity();

// Check if user is allowed to se entity
$guard = new sspmod_janus_UIguard($janus_config->getArray('access', array()));
$allowedUsers = $mcontroller->getUsers();
if(!(array_key_exists($userid, $allowedUsers) || $guard->hasPermission('allentities', null, $user->getType(), TRUE))) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

// Init template object
$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:editentity');

// Retrive current language
$language = $et->getLanguage();

$update = FALSE;
$note = '';

if(!empty($_POST)) {
    // Array for collecting addresses to notify
    $addresses = array();

    // Change entityID
    if(isset($_POST['entityid'])) {
        if(check_uri($_POST['entityid'])) {
            if($entity->setEntityid($_POST['entityid'])) {
                $update = TRUE;
                $note .= 'Changed entityID: ' . $_POST['entityid'] . '<br />';
                $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGEENTITYID';
            }
        } else {
            $msg = 'error_entity_not_url';
        }
    }

    // Metadata
    if(!empty($_POST['meta_value'])) {
        foreach($_POST['meta_value'] AS $k => $v) {
            // If field is boolean
            if(substr($k, -4) == 'TRUE') {
                $k = substr($k, 0, -5);
                $v = true;
            } else if(substr($k, -5) == 'FALSE') {
                $k = substr($k, 0, -6);
                $v = false;
            }
            if($mcontroller->addMetadata($k, $v)) {
                $update = TRUE;
                $note .= 'Metadata added: ' . $k . ' => ' . $v . '<br />';
            }
        }
    }

    // Update metadata
    foreach($_POST AS $key => $value) {
        //Metadata
        if(substr($key, 0, 14) == 'edit-metadata-') {
            if(!is_array($value)) {
                $newkey = substr($key, 14, strlen($key));

                // If field is boolean
                if(substr($newkey, -4) == 'TRUE') {
                    $newkey = substr($newkey, 0, -5);
                    $value = true;
                } else if(substr($newkey, -5) == 'FALSE') {
                    $newkey = substr($newkey, 0, -6);
                    $value = false;
                }

                if($mcontroller->updateMetadata($newkey, $value)) {
                    $update = TRUE;
                    $note .= 'Metadata edited: ' . $newkey . ' => ' . $value . '<br />';
                }
            }
        }
    }

    // Delete metadata
    if(isset($_POST['delete-metadata'])) {
        foreach($_POST['delete-metadata'] AS $data) {
            if($mcontroller->removeMetadata($data)) {
                $update = TRUE;
                $note .= 'Metadata deleted: ' . $data . '<br />';
            }
        }
    }

    // Add metadata from a URL.
    // NOTE. This will overwrite everything paster to the XML field
    if(isset($_POST['add_metadata_from_url'])) {
        if(!empty($_POST['meta_url'])) {
            if($mcontroller->setMetadataURL($_POST['meta_url'])) {
                $update = TRUE;
                $note .= 'Metadata URL set: ' . $_POST['meta_url'] . '<br />';
            }
            try {
                $res = @file_get_contents($_POST['meta_url']);
                if($res) {
                    $_POST['meta_xml'] = $res;
                    $note .= 'Import metadata from URL: ' . $_POST['meta_url'] . '<br />';
                } else {
                    $msg = 'error_import_metadata_url';
                }
            } catch(Exception $e) {
                SimpleSAML_Logger::warning('Janus: Failed to retrieve metadata. ' . $e->getMessage());
            }
        }
    }

    // Add metadata from pasted XML
    if(!empty($_POST['meta_xml'])) {
        if($entity->getType() == 'saml20-sp') {
            if($msg = $mcontroller->importMetadata20SP($_POST['meta_xml'], $update)) {
                $note .= 'Imported SAML 2.0 SP metadata succesfully<br />';
            }
        } else if($entity->getType() == 'saml20-idp') {
            if($msg = $mcontroller->importMetadata20IdP($_POST['meta_xml'], $update)) {
                $note .= 'Imported SAML 2.0 IdP metadata succesfully<br />';
            }
        } else {
            die('Type error');
        }
    }

    // Disable consent
    if(isset($_POST['add-consent'])) {
        $mcontroller->clearConsent();
        foreach($_POST['add-consent'] AS $key) {
            if($mcontroller->addDisableConsent($key)) {
                $update = TRUE;
                $note .= 'Consent disabled for: ' . $key . '<br />';
            }
        }
    }

    // Remote entities
    if(isset($_POST['addBlocked'])) {
        $mcontroller->setAllowedAll('no');
        $current = array_keys($mcontroller->getBlockedEntities());
        // Add the ones that are selected
        foreach($_POST['addBlocked'] AS $key) {
            if($mcontroller->addBlockedEntity($key)) {
                $update = TRUE;
                $note .= 'Remote entity added: ' . $key . '<br />';
            }
        }
        // Remove the ones that were, but are now no longer selected
        foreach($current as $entityid) {
            if (!in_array($entityid, $_POST['addBlocked'])) {
                if ($mcontroller->removeBlockedEntity($entityid)) {
                    $update = TRUE;
                    $node .= 'Existing entity removed: '. $entityid . '<br/>';
                }
            }
        }
        
    } else if (count($mcontroller->getBlockedEntities())) {
        // There were blocked entities but they were no longer posted; we should clear them all
        $mcontroller->clearBlockedEntities();
        $update = TRUE;
    }


    if(isset($_POST['addAllowed'])) {
        $mcontroller->setAllowedAll('no');
        $current = array_keys($mcontroller->getAllowedEntities());
        
        // Add the ones that are selected
        foreach($_POST['addAllowed'] AS $key) {
            if($mcontroller->addAllowedEntity($key)) {
                $update = TRUE;
                $note .= 'Remote entity added: ' . $key . '<br />';
            }
        }
        // Remove the ones that were, but are now no longer selected
        foreach($current as $entityid) {
            if (!in_array($entityid, $_POST['addAllowed'])) {
                if ($mcontroller->removeAllowedEntity($entityid)) {
                    $update = TRUE;
                    $node .= 'Existing entity removed: '. $entityid . '<br/>';
                }
            }
        }
    } else if (count($mcontroller->getAllowedEntities())) {
        // There were allowed entities but they were no longer posted; we should clear them all. 
        $mcontroller->clearAllowedEntities();
        $update = TRUE;
    }
    

    // Allowedal
    if(isset($_POST['allowall']) || isset($_POST['allownone'])) {
        if($mcontroller->setAllowedAll(isset($_POST['allowall'])?'yes':'no')) {
            $update = TRUE;
            $mcontroller->clearAllowedEntities();
            $mcontroller->clearBlockedEntities();
            $note .= 'Set block/allow all remote entities<br />';
        }
    }

    // Change workflow
    if(isset($_POST['entity_workflow'])) {
        if($entity->setWorkflow($_POST['entity_workflow'])) {
            $update = TRUE;
            $note .= 'Changed workflow: ' . $_POST['entity_workflow'] . '<br />';
            $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGESTATE-' . $_POST['entity_workflow'];
        }
    }
    
    // change ARPw
    if(isset($_POST['entity_arp'])) {
        if($entity->setArp($_POST['entity_arp'])) {
            $update = TRUE;
            $note .= 'Changed arp: ' . $_POST['entity_arp'] . '<br />';
            $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGEARP-' . $_POST['entity_arp'];
        }
    }

    // Change entity type
    if($entity->setType($_POST['entity_type'])) {
        $old_metadata = $mcontroller->getMetadata();
        
        // Get metadatafields for new type
        $nm_mb = new sspmod_janus_MetadatafieldBuilder(
            $janus_config->getArray('metadatafields.' . $_POST['entity_type'])
        );
        $new_metadata = $nm_mb->getMetadatafields();
        
        // Only remove fields specific to old type
        foreach($old_metadata AS $om) {
            if(!isset($new_metadata[$om->getKey()])) {
                $mcontroller->removeMetadata($om->getKey());
            }  
        }

        // Add all required fields for new type
        foreach($new_metadata AS $mf) {
            if (isset($mf->required) && $mf->required === true) {
                $mcontroller->addMetadata($mf->name, $mf->default);
                $update = true;
            }
        }

        $update = TRUE;
        $note .= 'Changed entity type: ' . $_POST['entity_type'] . '<br />';
    }

    // Set parent revision
    $entity->setParent($entity->getRevisionid());

    // Set user
    $entity->setUser($user->getUid());

    $norevision = array(
        'da' => 'Ingen revisionsnote',
        'en' => 'No revision note',
    );

    // Set revision note
    if(empty($_POST['revisionnote'])) {
        if (array_key_exists($language, $norevision)) {
            $entity->setRevisionnote($norevision[$language]);
        } else {
            $entity->setRevisionnote($norevision['en']);
        }
    } else {
        $entity->setRevisionnote($_POST['revisionnote']);
    }

    // Update entity if updated
    if($update) {
        $mcontroller->saveEntity();
        $mcontroller->loadEntity();
        $pm = new sspmod_janus_Postman();
        $addresses[] = 'ENTITYUPDATE-' . $eid;
        $pm->post('Entity updated - ' . $entity->getEntityid(), $entity->getRevisionnote() . '<br />' . $note, $addresses, $user->getUid());

        SimpleSAML_Utilities::redirect(
            SimpleSAML_Utilities::selfURLNoQuery(),            
            Array('eid' => $eid, 'msg' => $msg)            
        );
    }
}

// Get remote entities
if($entity->getType() == 'saml20-sp') {
    $loaded_entities = array_merge($autil->getEntitiesByStateType(null, 'saml20-idp'),
                                   $autil->getEntitiesByStateType(null, 'shib13-idp'));
} else if($entity->getType() == 'saml20-idp') {
    $loaded_entities = array_merge($autil->getEntitiesByStateType(null, 'saml20-sp'),
                                   $autil->getEntitiesByStateType(null, 'shib13-sp'));
} else if($entity->getType() == 'shib13-sp') {
    $loaded_entities = array_merge($autil->getEntitiesByStateType(null, 'saml20-idp'),
                                   $autil->getEntitiesByStateType(null, 'shib13-idp'));
} else if($entity->getType() == 'shib13-idp') {
    $loaded_entities = array_merge($autil->getEntitiesByStateType(null, 'saml20-sp'),
                                   $autil->getEntitiesByStateType(null, 'shib13-sp'));    
}

// Get metadatafields
$mfc = $janus_config->getArray('metadatafields.' . $entity->getType());
$mb = new sspmod_janus_MetadatafieldBuilder($mfc);
$et->data['metadatafields'] = $mb->getMetadatafields();

$remote_entities = array();

// Only parse name and description in current language
foreach($loaded_entities AS $entityRow) {
    
    $instance = new sspmod_janus_Entity($janus_config);
    $instance->setEid($entityRow["eid"]);
    $instance->setRevisionid($entityRow["revisionid"]);
    $instance->load();
    
    $value = array("name"=>$instance->getPrettyName(),
                   "description"=>$instance->getEntityId(),
                   );
    $key = $instance->getEntityId();
        
    unset($value2);
    if(isset($value['name'])) {
        if(is_array($value['name'])) {
            if(array_key_exists($language, $value['name'])) {
                $value2['name'][$language] = $value['name'][$language];
            } else {
                reset($value['name']);
                $value2['name'][$language] = 'No name in current language (' . current($value['name']) . ')';
            }
        } else {
            $value2['name'][$language] = $value['name'];
        }
    } else {
        $value2['name'][$language] = 'No name given';
    }
    if(isset($value['description'])) {
        if(is_array($value['description'])) {
            if(array_key_exists($language, $value['description'])) {
                $value2['description'][$language] = $value['description'][$language];
            } else {
                reset($value['description']);
                $value2['description'][$language] = 'No description in current language (' . current($value['description']) . ')';
            }
        } else {
            $value2['description'][$language] = $value['description'];
        }
    } else {
        $value2['description'][$language] = 'No description given';
    }
    $remote_entities[$key] = $value2;
}

/**
 *  Sort metadatafields according to order
 */
function cmp($a, $b) {
    if (!isset($a->order)) {
        return -1;
    }
    if (!isset($b->order)) {
        return 1;
    }
    if ($a->order == $b->order) {
        return 0;
    }
    return ($a->order < $b->order) ? -1 : 1;
}

/**
 * Sort metadata entries according to the order on the metadatafield
 */
function cmp2($a, $b) {
    global $et;

    if (!isset($et->data['metadatafields'][$a->getKey()])) {
        return -1;
    }
    $a_field = $et->data['metadatafields'][$a->getKey()];
    
    if (!isset($et->data['metadatafields'][$b->getKey()])) {
        return 1;
    }
    $b_field = $et->data['metadatafields'][$b->getKey()];

    if (!isset($a_field->order)) {
        return -1;
    }
    if (!isset($b_field->order)) {
        return 1;
    }
    if ($a_field->order == $b_field->order) {
        return 0;
    }
    return ($a_field->order < $b_field->order) ? -1 : 1;
}

// Sort metadatafields according to order
uasort($et->data['metadatafields'], 'cmp');

$et->data['metadata'] = $mcontroller->getMetadata();

// Sort metadata according to order
uasort($et->data['metadata'], 'cmp2');

// Get allowed workflows
$allowed_workflow = array();
$allowed_workflow[] = $entity->getWorkflow();
$workflowstates = array();
if (isset($workflow[$entity->getWorkflow()])) {
    $workflowstates = $janus_config->getValue('workflowstates');
    foreach($workflow[$entity->getWorkflow()] AS $k_wf => $v_wf) {
        $tmp = array_intersect($user->getType(), $v_wf['role']);
        if(!empty($tmp) || in_array('all', $v_wf['role'])) {
            $allowed_workflow[] = $k_wf;
        }
    }
} else {
    $workflowstates = array(
        $entity->getWorkflow() => array(
            'name' => array(
                'en' => $entity->getWorkflow()
            ),
            'description' => array(
                'en' => 'No description available. Workflow state `' . $entity->getWorkflow() . '` is not defined in the configuration file. This is probably an error. Contact your system administrator to get this error fixed.',    
            )
        )
    );
}

$arp = new sspmod_janus_ARP;

$et->data['entity_state'] = $entity->getWorkflow();
$et->data['entity_type'] = $entity->getType();
$et->data['revisionid'] = $entity->getRevisionid();
$et->data['types'] = $janus_config->getValue('types');
$et->data['workflowstates'] = $workflowstates;
$et->data['access'] = $janus_config->getValue('access');
$et->data['workflow'] = $allowed_workflow;
$et->data['entity'] = $entity;
$et->data['user'] = $user;
$et->data['uiguard'] = new sspmod_janus_UIguard($janus_config->getValue('access'));
$et->data['mcontroller'] = $mcontroller;
$et->data['blocked_entities'] = $mcontroller->getBlockedEntities();
$et->data['allowed_entities'] = $mcontroller->getAllowedEntities();
$et->data['disable_consent'] = $mcontroller->getDisableConsent();
$et->data['remote_entities'] = $remote_entities;
$et->data['arp_list'] = $arp->getARPList();
$et->data['attribute_fields'] = $janus_config->getValue('attributes');
$et->data['useblacklist'] = $janus_config->getValue('entity.useblacklist');
$et->data['usewhitelist'] = $janus_config->getValue('entity.usewhitelist');

$et->data['header'] = 'JANUS';
if(isset($msg)) {
    $et->data['msg'] = $msg;
}

$et->show();
?>
