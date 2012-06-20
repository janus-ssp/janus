<?php

// Initial import
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');

// Get data from config
/** @var $authenticationSource string */
$authenticationSource   = $janusConfig->getValue('auth', 'login-admin');
/** @var $userIdAttribute string */
$userIdAttribute        = $janusConfig->getValue('useridattr', 'eduPersonPrincipalName');

// Validate user
if ($session->isValid($authenticationSource)) {
    $attributes = $session->getAttributes();
    // Check if user id exists
    if (!isset($attributes[$userIdAttribute])) {
        throw new Exception('User ID is missing');
    }
    $userid = $attributes[$userIdAttribute][0];
    $user = new sspmod_janus_User($janusConfig->getValue('store'));
    $user->setUserid($userid);
    $user->load(sspmod_janus_User::USERID_LOAD);
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
    exit;
}

$importData  = $session->getData('string', 'import');
$importType = $session->getData('string', 'import_type');

if (!$importData && !$importType) {
    throw new SimpleSAML_Error_Exception('Nothing to import!');
}

if (!isset($_GET['eid'])) {
    throw new SimpleSAML_Error_Exception('No entity selected!');
}

// Revision not set, get latest
$entityController = new sspmod_janus_EntityController($janusConfig);
$entity = $entityController->setEntity(
    (string)(int)$_GET['eid']
);
if(!$entity) {
    throw new SimpleSAML_Error_Exception('Faulty entity selected');
}

$update = false;
$msg = '';
$note = '';
$oldMetadata = $entityController->arrayFlattenSep(':', $entityController->getMetaArray());
$et = new SimpleSAML_XHTML_Template($config, 'janus:importentity.php', 'janus:editentity');
$et->data['old'] = $oldMetadata;

if ($importType === 'xml') {
    if ($entity->getType() === 'saml20-sp') {
        $msg = $entityController->importMetadata20SP($importData, $update);
    } else if($entity->getType() === 'saml20-idp') {
        $msg = $entityController->importMetadata20IdP($importData, $update);
    } else {
        throw new SimpleSAML_Error_Exception($entity->getType() . ' is not a valid type to import');
    }
}
else if ($importType === 'json') {
    try {
        $metaStdClass = json_decode($importData);
        if ($metaStdClass) {
            $metaArray = convert_stdobject_to_array($metaStdClass);
            $metaArray = $entityController->arrayFlattenSep(':', $metaArray);

            if ($metaArray['entityid'] === $entityController->getEntity()->getEntityid()) {
                foreach ($metaArray as $key => $value) {
                    if ($entityController->hasMetadata($key)) {
                        $entityController->updateMetadata($key, $value);
                    } else {
                        $entityController->addMetadata($key, $value);
                    }
                }
                $update = TRUE;
                $msg = 'status_metadata_parsed_ok';
            }
            else {
                $msg = 'error_metadata_wrong_entity';
            }
        }
        else {
            $msg = 'error_not_valid_json';
        }
    }
    catch (Exception $e) {
        $msg = 'error_metadata_not_parsed';
    }
}
else {
    throw new SimpleSAML_Error_Exception("Unknown import type: '$importType'");
}

if (!empty($_POST) && isset($_POST['apply'])) {
    // Update entity if updated
    if ($update) {
        $entityController->saveEntity();
        $entityController->loadEntity();
        $entity = $entityController->getEntity();

        // Notify users who have asked to be updated when
        $pm = new sspmod_janus_Postman();
        $addresses[] = 'ENTITYUPDATE-' . $entity->getEid();
        $editLink = SimpleSAML_Module::getModuleURL(
            'janus/editentity.php',
            array(
                'eid'           => $entity->getEid(),
                'revisionid'    => $entity->getRevisionid())
        );
        $pm->post(
            'Entity updated - ' . $entity->getEntityid(),
            'Permalink: <a href="' . $editLink . '">' .
                    $editLink .
                '</a><br /><br />' .
                $entity->getRevisionnote() .
                '<br /><br />' .
                $note,
            $addresses,
            $user->getUid()
        );
    }

    $session->deleteData('string', 'meta_xml');
    $session->deleteData('string', 'meta_json');

    SimpleSAML_Utilities::redirect(
        SimpleSAML_Module::getModuleURL('janus/editentity.php'),
        array(
            'eid'           => $entity->getEid(),
            'revisionid'    => $entity->getRevisionid(),
        )
    );
    exit;
}

$et->data['update'] = $update;

$newMetadata = $entityController->arrayFlattenSep(':', $entityController->getMetaArray());
$et->data['new'] = $newMetadata;

$changes = janus_array_diff_recursive($newMetadata, $oldMetadata);
$et->data['changes'] = $changes;

$et->data['message'] = $msg;
$et->show();

function janus_array_diff_recursive($array1, $array2) {
    $diff = array();
    foreach ($array1 as $key => $value) {
        if (array_key_exists($key, $array2)) {
            if (is_array($value)) {
                $subDiff = janus_array_diff_recursive($value, $array2[$key]);
                if (count($subDiff)) {
                    $diff[$key] = $subDiff;
                }
            } else {
                if ($value != $array2[$key]) {
                    $diff[$key] = $value;
                }
            }
        } else {
            $diff[$key] = $value;
        }
    }
    return $diff;
}

function convert_stdobject_to_array($object)
{
    $object = (array)$object;

    foreach($object as $key => $value){
        if(is_array($value) || (is_object($value) && get_class($value)==='stdClass')){
            $object[$key] = convert_stdobject_to_array($value);
        }
    }
    return $object;
}