<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */
// Initial import
/** @var $session SimpleSAML_Session */
set_time_limit(180);
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$workflow = $janus_config->getValue('workflow_states');
$workflowstates = $janus_config->getValue('workflowstates');

try {
    $userModelFromDoctrine = sspmod_janus_DiContainer::getInstance()->getLoggedInUser();
} catch (Exception $ex) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'), $_GET);
}

function check_uri($uri)
{
    if (preg_match('/^[a-z][a-z0-9+-\.]*:.+$/i', $uri) == 1) {
        return TRUE;
    }
    return FALSE;
}

// Get Entity controller
$entityController = new sspmod_janus_EntityController($janus_config);

// Get the user
// @todo Replace this user object with '$userModelFromDoctrine'
$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setUserid($userModelFromDoctrine->getUsername());
$user->load(sspmod_janus_User::USERID_LOAD);

// Get Admin util which we use to retrieve entities
$adminUtil = new sspmod_janus_AdminUtil();


// @todo move to separate class
// Function to fix up PHP's messing up POST input containing dots, etc.
function getRealPOST()
{
    $vars = array();
    $input = file_get_contents("php://input");
    if (!empty($input)) {
        $pairs = explode("&", $input);
        foreach ($pairs as $pair) {
            $nv = explode("=", $pair);
            $name = urldecode($nv[0]);
            $value = urldecode($nv[1]);
            $name = explode('[', $name);
            if (count($name) > 1) {
                $subkey = substr($name[1], 0, -1);
                if (empty($subkey)) {
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
// We need the actual POST
$originalPost = $_POST;
// Fix the POST array. Metadata fields can contain . _ and more
$_POST = getRealPOST();

// Get correct revision
$revisionid = -1;
$msg = null;

// If post is set it has priority
if (!empty($_POST)) {
    if (!isset($_POST['eid']) | !isset($_POST['revisionid'])) {
        throw new SimpleSAML_Error_Exception('eid and revisionid parameter must be set');
    }
    $eid = $_POST['eid'];
    // Note that when saving an entity 'revision id' does mean the revision the the new version will be based on
    $parentRevisionId = $_POST['revisionid'];
} else if (!empty($_GET)) {
    if (!isset($_GET['eid'])) {
        throw new SimpleSAML_Error_Exception('eid parameter must be set');
    }
    $eid = $_GET['eid'];
    if (isset($_GET['revisionid'])) {
        $revisionid = $_GET['revisionid'];
    }
    if (isset($_GET['msg']) && !empty($_GET['msg'])) {
        $msg = $_GET['msg'];
    }
} else {
    throw new SimpleSAML_Error_Exception('eid and revisionid parameter must be set');
}

// Revisin id has been set. Fetch the correct version of the entity
if ($revisionid > -1) {
    if (!$entity = $entityController->setEntity($eid, $revisionid)) {
        throw new SimpleSAML_Error_Exception('Error in setEntity');
    }
} else {
    // Revision not set, get latest
    if (!$entity = $entityController->setEntity($eid)) {
        throw new SimpleSAML_Error_Exception('Error in setEntity');
    }
}
// load entity
$entityController->loadEntity();

// Check if user is allowed to se entity
$guard = new sspmod_janus_UIguard($janus_config->getArray('access', array()));
$allowedUsers = $entityController->getUsers();
if (!(array_key_exists($userModelFromDoctrine->getUsername(), $allowedUsers) || $guard->hasPermission('allentities', null, $user->getType(), TRUE))) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:editentity');

$language = $et->getLanguage();

$update = FALSE;
function markForUpdate()
{
    global $update;
    $update = true;
}

$note = '';

if (!empty($_POST)) {
    // Whether to redirect to importing.
    $redirectToImport = false;

    // Array for collecting addresses to notify
    $addresses = array();

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $session->getSessionId()) {
        SimpleSAML_Logger::warning('Janus: [SECURITY] CSRF token not found or does not match session id');
        throw new SimpleSAML_Error_Exception(
            '[SECURITY] CSRF token not found or did not match session id!'
        );
    }

    // Change entityID
    if (isset($_POST['entityid']) && $guard->hasPermission('changeentityid', $entity->getWorkflow(), $user->getType())) {
        $validateEntityId = $janus_config->getValue('entity.validateEntityId', true);
        if (!$validateEntityId || ($validateEntityId && check_uri($_POST['entityid']))) {
            $entityIdNeedsUpdating = $_POST['entityid'] != $entity->getEntityid();
            if ($entityIdNeedsUpdating) {
                $userController = new sspmod_janus_UserController($janus_config);
                if ($userController->isEntityIdInUse($_POST['entityid'], $errorMessage)) {
                    $msg = $errorMessage;
                } else {
                    if ($entity->setEntityid($_POST['entityid'])) {
                        markForUpdate();
                        $note .= 'Changed entityID: ' . $_POST['entityid'] . '<br />';
                        $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGEENTITYID';
                    }
                }
            }
        } else {
            $msg = 'error_entity_not_url';
        }
    }

    // Metadata
    if (!empty($_POST['meta_value']) && $guard->hasPermission('addmetadata', $entity->getWorkflow(), $user->getType())) {
        foreach ($_POST['meta_value'] AS $k => $v) {
            // If field is boolean
            if (substr($k, -4) == 'TRUE') {
                $k = substr($k, 0, -5);
                $v = true;
            } else if (substr($k, -5) == 'FALSE') {
                $k = substr($k, 0, -6);
                $v = false;
            }
            if ($entityController->addMetadata($k, $v)) {
                markForUpdate();
                $note .= 'Metadata added: ' . $k . ' => ' . $v . '<br />';
            }
        }
    }

    // Update metadata
    if ($guard->hasPermission('modifymetadata', $entity->getWorkflow(), $user->getType())) {
        foreach ($_POST AS $key => $value) {
            //Metadata
            if (substr($key, 0, 14) == 'edit-metadata-') {
                if (!is_array($value)) {
                    $newkey = substr($key, 14, strlen($key));

                    // If field is boolean
                    if (substr($newkey, -4) == 'TRUE') {
                        $newkey = substr($newkey, 0, -5);
                        $value = true;
                    } else if (substr($newkey, -5) == 'FALSE') {
                        $newkey = substr($newkey, 0, -6);
                        $value = false;
                    }

                    if ($entityController->updateMetadata($newkey, $value)) {
                        markForUpdate();
                        $note .= 'Metadata edited: ' . $newkey . ' => ' . $value . '<br />';
                    }
                }
            }
        }
    }

    // Delete metadata
    if (isset($_POST['delete-metadata']) && $guard->hasPermission('deletemetadata', $entity->getWorkflow(), $user->getType())) {
        foreach ($_POST['delete-metadata'] AS $data) {
            if ($entityController->removeMetadata($data)) {
                markForUpdate();
                $note .= 'Metadata deleted: ' . $data . '<br />';
            }
        }
    }

    // Add metadata from a URL.
    // NOTE. This will overwrite everything paster to the XML field
    if (isset($_POST['add_metadata_from_url']) && $guard->hasPermission('importmetadata', $entity->getWorkflow(), $user->getType())) {
        if (!empty($_POST['meta_url'])) {
            if ($entityController->setMetadataURL($_POST['meta_url'])) {
                markForUpdate();
                $note .= 'Metadata URL set: ' . $_POST['meta_url'] . '<br />';
            }
            try {
                $res = @file_get_contents($_POST['meta_url']);
                if ($res) {
                    $_POST['meta_xml'] = $res;
                    $note .= 'Import metadata from URL: ' . $_POST['meta_url'] . '<br />';
                } else {
                    $msg = 'error_import_metadata_url';
                }
            } catch (Exception $e) {
                SimpleSAML_Logger::warning('Janus: Failed to retrieve metadata. ' . $e->getMessage());
            }
        }
    }

    // Add metadata from pasted XML
    if (!empty($_POST['meta_xml']) && $guard->hasPermission('importmetadata', $entity->getWorkflow(), $user->getType())) {
        $redirectToImport = true;
        $session->setData('string', 'import_type', 'xml');
        $session->setData('string', 'import', $_POST['meta_xml']);
        if (!in_array($entity->getType(), array('saml20-sp', 'saml20-idp'))) {
            throw new SimpleSAML_Error_Exception($entity->getType() . ' is not a valid type for metadata import!');
        }
    }

    if (!empty($_POST['meta_json']) && $guard->hasPermission('importmetadata', $entity->getWorkflow(), $user->getType())) {
        $redirectToImport = true;
        function convert_stdobject_to_array($object)
        {
            $object = (array)$object;

            foreach ($object as $key => $value) {
                if (is_array($value) || (is_object($value) && get_class($value) === 'stdClass')) {
                    $object[$key] = convert_stdobject_to_array($value);
                }
            }
            return $object;
        }

        try {
            $metaStdClass = json_decode($_POST['meta_json']);
            if ($metaStdClass) {
                $metaArray = convert_stdobject_to_array($metaStdClass);
                $converter = sspmod_janus_DiContainer::getInstance()->getMetaDataConverter();
                $metaArray = $converter->execute($metaArray);
                if ($metaArray['entityid'] === $entityController->getEntity()->getEntityid()) {
                    $redirectToImport = true;
                    $session->setData('string', 'import_type', 'json');
                    $session->setData('string', 'import', $_POST['meta_json']);
                } else {
                    $msg = 'error_metadata_wrong_entity';
                }
            } else {
                $msg = 'error_not_valid_json';
            }
        } catch (Exception $e) {
            $msg = 'error_metadata_not_parsed';
        }
    }

    // Disable consent
    if ($_POST['consent-changed'] && $guard->hasPermission('disableconsent', $entity->getWorkflow(), $user->getType())) {
        $entityController->clearConsent();
        markForUpdate();
        foreach ($_POST['add-consent'] AS $key) {
            if ($entityController->addDisableConsent($key)) {
                $note .= 'Consent disabled for: ' . $key . '<br />';
            }
        }
    }

    // Remote entities
    if ($guard->hasPermission('blockremoteentity', $entity->getWorkflow(), $user->getType())) {
        if (isset($_POST['addBlocked'])) {
            $entityController->setAllowedAll('no');
            $current = array_keys($entityController->getBlockedEntities());
            // Add the ones that are selected
            foreach ($_POST['addBlocked'] AS $key) {
                if ($entityController->addBlockedEntity($key)) {
                    markForUpdate();
                    $note .= 'Remote entity added: ' . $key . '<br />';
                }
            }
            // Remove the ones that were, but are now no longer selected
            foreach ($current as $entityid) {
                if (!in_array($entityid, $_POST['addBlocked'])) {
                    if ($entityController->removeBlockedEntity($entityid)) {
                        markForUpdate();
                        $note .= 'Existing entity removed: ' . $entityid . '<br/>';
                    }
                }
            }

        } else if (count($entityController->getBlockedEntities())) {
            // There were blocked entities but they were no longer posted; we should clear them all
            $entityController->clearBlockedEntities();
            markForUpdate();
        }
    }


    if ($guard->hasPermission('blockremoteentity', $entity->getWorkflow(), $user->getType())) {
        if (isset($_POST['addAllowed'])) {
            $entityController->setAllowedAll('no');
            $current = array_keys($entityController->getAllowedEntities());

            // Add the ones that are selected
            foreach ($_POST['addAllowed'] AS $key) {
                if ($entityController->addAllowedEntity($key)) {
                    markForUpdate();
                    $note .= 'Remote entity added: ' . $key . '<br />';
                }
            }
            // Remove the ones that were, but are now no longer selected
            foreach ($current as $entityid) {
                if (!in_array($entityid, $_POST['addAllowed'])) {
                    if ($entityController->removeAllowedEntity($entityid)) {
                        markForUpdate();
                        $note .= 'Existing entity removed: ' . $entityid . '<br/>';
                    }
                }
            }
        } else if (count($entityController->getAllowedEntities())) {
            // There were allowed entities but they were no longer posted; we should clear them all.
            $entityController->clearAllowedEntities();
            markForUpdate();
        }
    }


    // Allowedal
    if ((isset($_POST['allowall']) || isset($_POST['allownone'])) && $guard->hasPermission('blockremoteentity', $entity->getWorkflow(), $user->getType())) {
        if ($entityController->setAllowedAll(isset($_POST['allowall']) ? 'yes' : 'no')) {
            markForUpdate();
            $entityController->clearAllowedEntities();
            $entityController->clearBlockedEntities();
            $note .= 'Set block/allow all remote entities<br />';
        }
    }

    // Change workflow
    if (isset($_POST['entity_workflow']) && $guard->hasPermission('changeworkflow', $entity->getWorkflow(), $user->getType())) {
        if ($entity->setWorkflow($_POST['entity_workflow'])) {
            markForUpdate();
            $note .= 'Changed workflow: ' . $_POST['entity_workflow'] . '<br />';
            $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGESTATE-' . $_POST['entity_workflow'];
        }
    }

    /*
     * change ARP. We have three different situations:
     *
     * 1) user has indicated that this SP has no APR (arp_no_arp_attributes)
     * 2) user has indicated the ARP attributes values (arp_attributes)
     * 3) user has indicated an empty ARP (so we have no POST parameters at all)
     *
     */

    if (isset($originalPost['arp_no_arp_attributes'])) {
        $arpAttributes = null;
    } elseif (isset($originalPost['arp_attributes'])) {
        $arpAttributes = $originalPost['arp_attributes'];
    } else {
        $arpAttributes = array();
    }
    if ($entity->setArpAttributes($arpAttributes)) {
        markForUpdate();
        $note .= 'Changed arpAttributes: ' . $originalPost['arp_attributes'] . '<br />';
        $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGEARP-' . $originalPost['arp_attributes'];
    }


    // change Manipulation
    if (isset($_POST['entity_manipulation']) && $guard->hasPermission('changemanipulation', $entity->getWorkflow(), $user->getType())) {
        $manipulationCode = $_POST['entity_manipulation'];

        $lintFile = tempnam(sys_get_temp_dir(), 'lint');
        file_put_contents($lintFile, '<?php ' . $manipulationCode);

        $returnCode = null;
        $lintOutput = null;
        exec("php -d error_reporting=E_ALL -l $lintFile", $lintOutput, $returnCode);

        unlink($lintFile);

        if ((int)$returnCode === 0) {
            if ($entity->setManipulation($manipulationCode)) {
                markForUpdate();
                $note .= 'Changed manipulation: ' . $_POST['entity_manipulation'] . '<br />';
                $addresses[] = 'ENTITYUPDATE-' . $eid . '-CHANGEMANIPULATION-' . $_POST['entity_manipulation'];
            }
        } else {
            $msg = "error_manipulation_syntax";
            array_pop($lintOutput);
            $lintOutput = str_replace("in $lintFile", '', implode(PHP_EOL, $lintOutput));
            $session->setData('string', 'manipulation_syntax_errors', $lintOutput);
            $session->setData('string', 'manipulation_code', $manipulationCode);
        }
    }

    // Change entity type
    if ($entity->setType($_POST['entity_type']) && $guard->hasPermission('changeentitytype', $entity->getWorkflow(), $user->getType())) {
        $old_metadata = $entityController->getMetadata();

        // Get metadatafields for new type
        $nm_mb = new sspmod_janus_MetadatafieldBuilder(
            $janus_config->getArray('metadatafields.' . $_POST['entity_type'])
        );
        $new_metadata = $nm_mb->getMetadatafields();

        // Only remove fields specific to old type
        foreach ($old_metadata AS $om) {
            if (!isset($new_metadata[$om->getKey()])) {
                $entityController->removeMetadata($om->getKey());
            }
        }

        // Add all required fields for new type
        foreach ($new_metadata AS $mf) {
            if (isset($mf->required) && $mf->required === true) {
                $entityController->addMetadata($mf->name, $mf->default);
                markForUpdate();
            }
        }

        markForUpdate();
        $note .= 'Changed entity type: ' . $_POST['entity_type'] . '<br />';
    }

    // Set parent revision
    $entity->setParent($parentRevisionId);

    // Set user
    $entity->setUser($user->getUid());

    // Set revision note
    if (empty($_POST['revisionnote'])) {
        if ($janus_config->getBoolean('revision.notes.required', false)) {
            $msg = 'error_revision_note_is_required';
        } else {
            $entity->setRevisionnote('No revision note');
        }
    } else {
        $entity->setRevisionnote($_POST['revisionnote']);
    }

    // Update entity if updated
    if ($update) {
        $entityController->saveEntity();
        $entityController->loadEntity();
        $pm = new sspmod_janus_Postman();
        $addresses[] = 'ENTITYUPDATE-' . $eid;
        $directlink = SimpleSAML_Module::getModuleURL('janus/editentity.php', array('eid' => $entity->getEid(), 'revisionid' => $entity->getRevisionid()));
        $pm->post('Entity updated - ' . $entity->getEntityid(), 'Permalink: <a href="' . $directlink . '">' . $directlink . '</a><br /><br />' . $entity->getRevisionnote() . '<br /><br />' . $note, $addresses, $user->getUid());
    }

    if ($redirectToImport) {
        $entity = $entityController->getEntity();
        SimpleSAML_Utilities::redirect(
            SimpleSAML_Module::getModuleURL('janus/importentity.php'),
            array(
                'eid' => $entity->getEid(),
            )
        );
    } else {
        SimpleSAML_Utilities::redirect(
            SimpleSAML_Utilities::selfURLNoQuery(),
            Array(
                'eid' => $eid,
                'msg' => $msg,
                'selectedtab' => isset($_POST['selectedtab']) ? (int)$_POST['selectedtab'] : 0,
            )
        );
    }
}

// Get remote entities
if ($entity->getType() == 'saml20-sp') {
    $remoteTypes = array('saml20-idp', 'shib13-idp');
} else if ($entity->getType() == 'saml20-idp') {
    $remoteTypes = array('saml20-sp', 'shib13-sp');
} else if ($entity->getType() == 'shib13-sp') {
    $remoteTypes = array('saml20-idp', 'shib13-idp');
} else if ($entity->getType() == 'shib13-idp') {
    $remoteTypes = array('saml20-sp', 'shib13-sp');
} else {
    throw new Exception('New type');
}

$remoteEntities = array();
foreach ($remoteTypes as $remoteType) {
    $remoteEntities = array_merge($remoteEntities, $adminUtil->getEntitiesByStateType(null, $remoteType));
}

if ($guard->hasPermission('allentities', null, $user->getType(), TRUE)) {
    $userEntities = $remoteEntities;
} else {
    $userEntities = $adminUtil->getEntitiesFromUser($user->getUid());
}

$reverseBlockedEntities = $adminUtil->getReverseBlockedEntities($entity, $userEntities);

// Get metadatafields
$mfc = $janus_config->getArray('metadatafields.' . $entity->getType());
$mb = new sspmod_janus_MetadatafieldBuilder($mfc);
$et->data['metadatafields'] = $mb->getMetadatafields();

$remote_entities = array();
$remote_entities_acl_sorted = array();

// Only parse name and description in current language
foreach ($remoteEntities AS $remoteEntityRow) {

    $remoteEntity = new sspmod_janus_Entity($janus_config);
    $remoteEntity->setEid($remoteEntityRow["eid"]);
    $remoteEntity->setRevisionid($remoteEntityRow["revisionid"]);
    $remoteEntity->load();

    $remoteEntityFormatted = array(
        'eid' => $remoteEntity->getEid(),
        'revisionid' => $remoteEntity->getRevisionid(),
        'type' => $remoteEntity->getType(),
    );

    // Format the name for the remote entity
    $remoteEntityName = $remoteEntity->getPrettyName();
    if (isset($remoteEntityName)) {
        if (is_array($remoteEntityName)) {
            if (array_key_exists($language, $remoteEntityName)) {
                $remoteEntityFormatted['name'][$language] = $remoteEntityName[$language];
            } else {
                reset($remoteEntityName);
                $remoteEntityFormatted['name'][$language] = 'No name in current language (' . current($remoteEntityName) . ')';
            }
        } else {
            $remoteEntityFormatted['name'][$language] = $remoteEntityName;
        }
    } else {
        $remoteEntityFormatted['name'][$language] = 'No name given';
    }

    // Format the description for the remote entity
    $remoteEntityDescription = $remoteEntity->getEntityId();
    if (isset($remoteEntityDescription)) {
        if (is_array($remoteEntityDescription)) {
            if (array_key_exists($language, $remoteEntityDescription)) {
                $remoteEntityFormatted['description'][$language] = $remoteEntityDescription[$language];
            } else {
                reset($remoteEntityDescription);
                $remoteEntityFormatted['description'][$language] = 'No description in current language (' . current($remoteEntityDescription) . ')';
            }
        } else {
            $remoteEntityFormatted['description'][$language] = $remoteEntityDescription;
        }
    } else {
        $remoteEntityFormatted['description'][$language] = 'No description given';
    }

    // Pass along a text color if available
    if (isset($workflowstates[$remoteEntity->getWorkflow()]['textColor'])) {
        $remoteEntityFormatted['textColor'] = $workflowstates[$remoteEntity->getWorkflow()]['textColor'];
    }

    // Pass along whether the remote entity has blocked the current entity
    $remoteEntityFormatted['blocked'] = false;
    foreach ($reverseBlockedEntities as $reverseBlockedEntity) {
        if ($reverseBlockedEntity['eid'] === $remoteEntity->getEid()) {
            $remoteEntityFormatted['blocked'] = true;
        }
    }

    // Whether the current user can edit the remote entity
    $remoteEntityFormatted['editable'] = false;
    foreach ($userEntities as $userEntity) {
        if ($userEntity['eid'] === $remoteEntity->getEid()) {
            $remoteEntityFormatted['editable'] = true;
        }
    }

    $remote_entities[$remoteEntity->getEntityId()] = $remoteEntityFormatted;
    $remote_entities_acl_sorted[$remoteEntity->getEntityId()] = $remoteEntityFormatted;
}

/**
 *  Sort metadatafields according to name
 */
function cmp($a, $b)
{
    return strcasecmp($a->name, $b->name);
}

/**
 * Sort metadata entries according to name
 */
function cmp2($a, $b)
{
    return strcasecmp($a->getKey(), $b->getkey());
}

/*
 * Sort remote entries based on the fact of the ACL is allowed
 */

function cmpByAcl($a, $b) {
    global $mcontroller, $language;

    $allowedEntities = $mcontroller->getAllowedEntities();
    $aAllowed = array_key_exists($a['eid'], $allowedEntities);
    $bAllowed = array_key_exists($b['eid'], $allowedEntities);
    if (($aAllowed && $bAllowed) || (!$aAllowed && !$bAllowed)) {
        return strcasecmp($a['name'][$language],$b['name'][$language]);
    }
    return $aAllowed ? -1 : 1;
}

// Sort metadatafields according to name
uasort($et->data['metadatafields'], 'cmp');

$et->data['metadata'] = $entityController->getMetadata();

//sort remote entities for acl based on blocked or not
uasort($et->data['metadata'], 'cmp2');

// Sort remote enties based on acl allowed
uasort($remote_entities_acl_sorted, 'cmpByAcl');

// Get allowed workflows
$allowed_workflow = array();
$allowed_workflow[] = $entity->getWorkflow();
$workflowstates = array();
if (isset($workflow[$entity->getWorkflow()])) {
    $workflowstates = $janus_config->getValue('workflowstates');
    foreach ($workflow[$entity->getWorkflow()] AS $k_wf => $v_wf) {
        $tmp = array_intersect($user->getType(), $v_wf['role']);
        if (!empty($tmp) || in_array('all', $v_wf['role'])) {
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

require __DIR__ . '/editentity/revisions.php';
addRevisionCompare($et, $eid);

require __DIR__ . '/editentity/arp.php';
addArpConfiguration($et, $janus_config);

$et->data['entity_state'] = $entity->getWorkflow();
$et->data['entity_type'] = $entity->getType();
$et->data['revisionid'] = $entity->getRevisionid();
$et->data['types'] = $janus_config->getValue('types');
$et->data['workflowstates'] = $workflowstates;
$et->data['access'] = $janus_config->getValue('access');
$et->data['workflow'] = $allowed_workflow;
$et->data['entity'] = $entity;
$et->data['user'] = $user;
$et->data['uiguard'] = $guard;
$et->data['mcontroller'] = $entityController;
$et->data['blocked_entities'] = $entityController->getBlockedEntities();
$et->data['allowed_entities'] = $entityController->getAllowedEntities();
$et->data['disable_consent'] = $entityController->getDisableConsent();
$et->data['remote_entities'] = $remote_entities;
$et->data['remote_entities_acl_sorted'] = $remote_entities_acl_sorted;
$et->data['arp_attributes'] = $janus_config->getValue('attributes');
$et->data['useblacklist'] = $janus_config->getValue('entity.useblacklist');
$et->data['usewhitelist'] = $janus_config->getValue('entity.usewhitelist');
$et->data['selectedtab'] = isset($_GET['selectedtab']) ? (int)$_GET['selectedtab'] : 0;

$et->data['header'] = 'JANUS';
if (isset($msg)) {
    $et->data['msg'] = $msg;
}
$et->data['session'] = $session;

$et->show();
?>
