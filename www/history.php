<?php
/**
 * History script
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.5.1
 */

// Initial setup
$session      = SimpleSAML_Session::getInstance();
$config       = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
$authsource   = $janus_config->getValue('auth', 'login-admin');
$useridattr   = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');
$et           = new SimpleSAML_XHTML_Template(
    $config, 
    'janus:editentity.php', 
    'janus:editentity'
);

// Validate user
if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr])) {
        throw new Exception('User ID is missing');
    }
    $userid = $attributes[$useridattr][0];
} else {
    echo $et->t('error_no_access');
    exit;
}

// Get Entity controller
$mcontroller = new sspmod_janus_EntityController($janus_config);

// Get the user
$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setUserid($userid);
$user->load(sspmod_janus_User::USERID_LOAD);

// Get the correct entity
$eid = $_GET['eid'];

if (!$entity = $mcontroller->setEntity($eid)) {
    throw new SimpleSAML_Error_Exception('Error in setEntity');
}
$workflowstates = $janus_config->getValue('workflowstates');
// load entity
$mcontroller->loadEntity();

// Check if user is allowed to se entity
$allowedUsers = $mcontroller->getUsers();
$uiguard      = new sspmod_janus_UIguard($janus_config->getValue('access'));
$output       = '';
$wfstate      = $entity->getWorkflow();

if (   (array_key_exists($userid, $allowedUsers) 
    || $uiguard->hasPermission('allentities', null, $user->getType(), true)) 
    && $uiguard->hasPermission('entityhistory', $wfstate, $user->getType())
) {
    $history_size = $mcontroller->getHistorySize();
    $history      = $mcontroller->getHistory(10, $history_size);

    foreach ($history AS $data) {
        $rid    = $data->getRevisionid();
        $rnote  = $data->getRevisionnote();
        $output .= '<a href="?eid=' .$data->getEid(). '&revisionid=' .$rid. '">' . 
            $et->t('tab_edit_entity_connection_revision'). ' ' .$rid. '</a>';
        $output .= (strlen($rnote) > 80) 
            ? ' - '. substr($rnote, 0, 79) . '...' : ' - '. $rnote;
        // Show edit user if present
        $user->setUid($data->getUser());
        if($user->load()) {
            $output .= ' - ' . $user->getUserid();
        }
        $output .= ' - ' . date('Y-m-d H:i', strtotime($data->getCreated()));
        
        if(isset($workflowstates[$data->getWorkflow()]['name'][$et->getLanguage()])) {
            $curLang = $et->getLanguage();
        } else {
            $curLang = 'en';
        }

        if (isset($workdlowstates[$data->getWorkflow()]['name'][$curLang])) {
            $output .= ' - ' . $workflowstates[$data->getWorkflow()]['name'][$curLang];
        } else {
            $output .= ' - ' . $data->getWorkflow();
        }
        $output .= '<br>';
    }
} else {
    $output .= $et->t('error_no_access');
}
echo $output;
