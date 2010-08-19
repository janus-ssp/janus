<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */
error_reporting(E_ALL);
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

// Get metadata to present remote entitites
$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
// Get Entity controller
$mcontroller = new sspmod_janus_EntityController($janus_config);

// Get the user
$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setUserid($userid);
$user->load(sspmod_janus_User::USERID_LOAD);

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
$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:janus');

// Retrive current language
$language = $et->getLanguage();

$update = FALSE;
$note = '';

if(!empty($_POST)) {
    // Change entityID
    if(isset($_POST['entityid'])) {
        if($entity->setEntityid($_POST['entityid'])) {
            $update = TRUE;
            $note .= 'Changed entityID: ' . $_POST['entityid'] . '<br />';
        }
    }

    // Change entity type
    if($entity->setType($_POST['entity_type'])) {
        $update = TRUE;
        $note .= 'Changed entity type: ' . $_POST['entity_type'] . '<br />';
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
            if($msg = $mcontroller->importMetadata20SP($_POST['meta_xml'])) {
                $update = TRUE;
                $note .= 'Imported SAML 2.0 SP metadata succesfully<br />';
            }
        } else if($entity->getType() == 'saml20-idp') {
            if($msg = $mcontroller->importMetadata20IdP($_POST['meta_xml'])) {
                $update = TRUE;
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
    if(isset($_POST['add'])) {
        $mcontroller->setAllowedAll('yes');
        $mcontroller->setAllowedAll('no');
        foreach($_POST['add'] AS $key) {
            if($mcontroller->addBlockedEntity($key)) {
                $update = TRUE;
                $note .= 'Remote entity added: ' . $key . '<br />';
            }
        }
    }

    // Allowedal
    if(isset($_POST['allowedall'])) {
        if($mcontroller->setAllowedAll('yes')) {
            $update = TRUE;
            $note .= 'Set allow all remote entities<br />';
        }
    } else {
        if($mcontroller->setAllowedAll('no')) {
            $update = TRUE;
            $note .= 'Removed set allow all remote entities<br />';
        }
    }

    // Change workflow
    if(isset($_POST['entity_workflow'])) {
        if($entity->setWorkflow($_POST['entity_workflow'])) {
            $update = TRUE;
            $note .= 'Changed workflow: ' . $_POST['entity_workflow'] . '<br />';
        }
    }
    
    // change ARPw
    if(isset($_POST['entity_arp'])) {
        if($entity->setArp($_POST['entity_arp'])) {
            $update = TRUE;
            $note .= 'Changed arp: ' . $_POST['entity_arp'] . '<br />';
        }
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
        $pm->post('Entity updated - ' . $entity->getEntityid(), $entity->getRevisionnote() . '<br />' . $note, 'ENTITYUPDATE-'.$entity->getEid(), $user->getUid());

        SimpleSAML_Utilities::redirect(
            SimpleSAML_Utilities::selfURLNoQuery(),            
            Array('eid' => $eid)            
        );
    }
}

// Get remote entities
if($entity->getType() == 'saml20-sp') {
    $remote_entities = $metadata->getList('saml20-idp-remote');
    $remote_entities = array_merge($metadata->getList('shib13-idp-remote'), $remote_entities);
    $et->data['metadata_fields'] = $janus_config->getValue('metadatafields.saml20-sp');
} else if($entity->getType() == 'saml20-idp') {
    $remote_entities = $metadata->getList('saml20-sp-remote');
    $remote_entities = array_merge($metadata->getList('shib13-sp-remote'), $remote_entities);
    $et->data['metadata_fields'] = $janus_config->getValue('metadatafields.saml20-idp');
} else if($entity->getType() == 'shib13-sp') {
    $remote_entities = $metadata->getList('saml20-idp-remote');
    $remote_entities = array_merge($metadata->getList('shib13-idp-remote'), $remote_entities);
    $et->data['metadata_fields'] = $janus_config->getValue('metadatafields.saml20-sp');
} else if($entity->getType() == 'shib13-idp') {
    $remote_entities = $metadata->getList('saml20-sp-remote');
    $remote_entities = array_merge($metadata->getList('shib13-sp-remote'), $remote_entities);
    $et->data['metadata_fields'] = $janus_config->getValue('metadatafields.saml20-idp');
}

// Only parse name and description in current language
foreach($remote_entities AS $key => $value) {
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

// Sorting functions
function cmp($a, $b) {
    if ($a['order'] == $b['order']) {
        return 0;
    }
    return ($a['order'] < $b['order']) ? -1 : 1;
}

function cmp2($a, $b) {
    global $et;

    $a_base_key = $et->data['metadata_base_field_names'][$a->getKey()];
    $b_base_key = $et->data['metadata_base_field_names'][$b->getKey()];

    if(!isset($et->data['metadata_fields'][$a_base_key]) || !isset($et->data['metadata_fields'][$a_base_key]['order'])) {
        return 1;
    }
    else if(!isset($et->data['metadata_fields'][$b_base_key]) || !isset($et->data['metadata_fields'][$b_base_key]['order'])) {
        return -1;
    }
    else {
        $aorder = $et->data['metadata_fields'][$a_base_key]['order'];
        $border = $et->data['metadata_fields'][$b_base_key]['order'];        

        if ($aorder == $border) {
            return 0;
        }
        return ($aorder < $border) ? -1 : 1;
    }
}

// Sort metadatafields according to order
uasort($et->data['metadata_fields'], 'cmp');

$et->data['metadata'] = $mcontroller->getMetadata();

$et->data['metadata_base_field_names'] = array();

foreach($et->data['metadata'] AS $data) {
    $key = $data->getKey();
    $key_splitted = explode(':', $key);
    $possible_supported_idiom = array_pop($key_splitted);
    $possible_metafield_key = str_replace(':'.$possible_supported_idiom,'',$key);
    if(isset($et->data['metadata_fields'][$possible_metafield_key])) {
        $et->data['metadata_base_field_names'][$key] = $possible_metafield_key;
    }
    else {
        $et->data['metadata_base_field_names'][$key] = $key;
    }
}

// Sort metadata according to order
uasort($et->data['metadata'], 'cmp2');

// Get allowed workflows
$allowed_workflow = array();
$allowed_workflow[] = $entity->getWorkflow();
foreach($workflow[$entity->getWorkflow()] AS $k_wf => $v_wf) {
    $tmp = array_intersect($user->getType(), $v_wf['role']);
    if(!empty($tmp) || in_array('all', $v_wf['role'])) {
        $allowed_workflow[] = $k_wf;
    }
}

$arp = new sspmod_janus_ARP;

$et->data['entity_state'] = $entity->getWorkflow();
$et->data['entity_type'] = $entity->getType();
$et->data['revisionid'] = $entity->getRevisionid();
$et->data['types'] = $janus_config->getValue('types');
$et->data['workflowstates'] = $janus_config->getValue('workflowstates');
$et->data['access'] = $janus_config->getValue('access');
$et->data['workflow'] = $allowed_workflow;
$et->data['entity'] = $entity;
$et->data['user'] = $user;
$et->data['uiguard'] = new sspmod_janus_UIguard($janus_config->getValue('access'));
$et->data['mcontroller'] = $mcontroller;
$et->data['blocked_entities'] = $mcontroller->getBlockedEntities();
$et->data['disable_consent'] = $mcontroller->getDisableConsent();
$et->data['remote_entities'] = $remote_entities;
$et->data['arp_list'] = $arp->getARPList();

$et->data['header'] = 'JANUS';
if(isset($msg)) {
    $et->data['msg'] = $msg;
}

$et->show();
?>
