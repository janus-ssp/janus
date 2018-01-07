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
 * @link       http://github.com/janus-ssp/janus/
 * @since      File available since Release 1.5.1
 */

require __DIR__ . '/_includes.php';

// Initial setup
$config       = SimpleSAML_Configuration::getInstance();
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();
$authsource   = $janus_config->getValue('auth', 'login-admin');
$useridattr   = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');
$et           = new SimpleSAML_XHTML_Template(
    $config, 
    'janus:editentity.php', 
    'janus:editentity'
);

$as = new \SimpleSAML\Auth\Simple($authsource);

// Validate user
if ($as->isAuthenticated()) {
    $attributes = $as->getAttributes();
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
$mcontroller = sspmod_janus_DiContainer::getInstance()->getEntityController();

// Get the user
$user = new sspmod_janus_User();
$user->setUserid($userid);
$user->load(sspmod_janus_User::USERID_LOAD);

$eid = $_GET['eid'];
$currentRevisionId = $_GET['currentRevisionId'];
$historyTab = $_GET['historyTab'];
if (!$entity = $mcontroller->setEntity($eid)) {
    throw new SimpleSAML_Error_Exception('Error in setEntity');
}
$workflowstates = $janus_config->getValue('workflowstates');
// load entity
$mcontroller->loadEntity();

// Check if user is allowed to se entity
$allowedUsers = $mcontroller->getUsers();
$output       = '';
$securityContext = sspmod_janus_DiContainer::getInstance()->getSecurityContext();

if (   (array_key_exists($userid, $allowedUsers) 
    || $securityContext->isGranted('allentities'))
    && $securityContext->isGranted('entityhistory', $entity)
) {
    $history_size = $mcontroller->getHistorySize();
    $history      = $mcontroller->getHistory(10, $history_size);

    foreach ($history AS $data) {
        $rid    = $data->getRevisionid();
        $rnote  = $data->getRevisionnote();
        $output .= '<section class="revision"><a href="?eid=' .$data->getEid(). '&revisionid=' .$rid. '">' .
            $et->t('tab_edit_entity_connection_revision'). ' ' .$rid. '</a>';
        if ($data->getRevisionid() !== $currentRevisionId) {
            $output .= ' - <a  class="janus_button" href="?compareRevision=true&amp;eid='. $data->getEid() .'&amp;compareRevisiondid='. $data->getRevisionid() . '&amp;revisionid=' . $currentRevisionId . '&amp;selectedtab='.$historyTab.'">Revision history</a>';
        }

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

        if (isset($workflowstates[$data->getWorkflow()]['name'][$curLang])) {
            $output .= ' - ' . $workflowstates[$data->getWorkflow()]['name'][$curLang];
        } else {
            $output .= ' - ' . $data->getWorkflow();
        }
        $output .= '</revision>';
    }
} else {
    $output .= $et->t('error_no_access');
}
echo $output;
