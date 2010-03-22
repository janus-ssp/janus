<?php
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

// Get the correct entity
$eid = $_GET['eid'];

if(!$entity = $mcontroller->setEntity($eid)) {
    die('Error in setEntity');
}
// load entity
$mcontroller->loadEntity();

// Check if user is allowed to se entity
$allowedUsers = $mcontroller->getUsers();
if(!array_key_exists($userid, $allowedUsers)) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

// Init template object
$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:janus');

// Retrive current language
$language = $et->getLanguage();

$update = FALSE;
$note = '';

$uiguard = new sspmod_janus_UIguard($janus_config->getValue('access'));
$wfstate = $entity->getWorkflow();

/* The template is only used for translations */
$et = new SimpleSAML_XHTML_Template($config, 'janus:editentity.php', 'janus:janus');

if ($uiguard->hasPermission('entityhistory', $wfstate, $user->getType())) {

    $history_size = $mcontroller->getHistorySize();
    $history = $mcontroller->getHistory(10, $history_size);

    foreach($history AS $data) {
        echo '<a href="?eid='. $data->getEid() .'&revisionid='. $data->getRevisionid().'">'. $et->t('tab_edit_entity_connection_revision') .' '. $data->getRevisionid() .'</a>';
        if (strlen($data->getRevisionnote()) > 80) {
            echo ' - '. substr($data->getRevisionnote(), 0, 79) . '...';
        } else {
            echo ' - '. $data->getRevisionnote();
        }
        echo '<br>';
    }

} else {
    echo $this->t('error_no_access');
}
?>
