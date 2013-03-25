<?php
/**
 * No user created main file
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Site
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Lorenzo Gil Sanchez <lgs@yaco.es>
 * @author     Sixto Martín <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
define('SELECTED_TAB_USERDATA', 'userdata');
define('SELECTED_TAB_ENTITIES', 'entities');
define('SELECTED_TAB_ARPADMIN', 'arpAdmin');
define('SELECTED_TAB_MESSAGE', 'message');
define('SELECTED_SUBTAB_MESSAGE_INBOX', 'message-inbox');
define('SELECTED_SUBTAB_MESSAGE_SUBSCRIPTIONS', 'message-subscriptions');
define('SELECTED_TAB_ADMIN', 'admin');
define('SELECTED_SUBTAB_ADMIN_USERS', 'admin-users');
define('SELECTED_SUBTAB_ADMIN_ENTITIES', 'admin-entities');
define('SELECTED_TAB_FEDERATION', 'federation');

define('TAB_AJAX_CONTENT_PREFIX', 'ajax-content/');

$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

// Validate user
if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    redirect(SimpleSAML_Module::getModuleURL('janus/index.php'), array(), IS_AJAX);
}

function check_uri ($uri)
{
    if (preg_match('/^[a-z][a-z0-9+-\.]*:.+$/i', $uri) == 1) {
        return TRUE;
    }
    return FALSE;
}

/**
 * Ajax compatible redirect method
 *
 * @param string $url
 * @param array $params
 * @param bool $isAjax
 */
function redirect($url, array $params = array(), $isAjax = false) {
    if ($isAjax) {
        $redirectUrl = str_replace(TAB_AJAX_CONTENT_PREFIX, '', $url) . '?' . http_build_query($params);
        die('<script type="text/javascript">window.location =\'' . $redirectUrl . '\';</script>');
    } else {
        SimpleSAML_Utilities::redirect($url, $params);
    }
}

$mcontrol = new sspmod_janus_UserController($janus_config);
$pm = new sspmod_janus_Postman();

if(!$user = $mcontrol->setUser($userid)) {
    throw new SimpleSAML_Error_Exception('Error in setUser');
}

// Note: $param variable is provided by SimpleSaml but only if there actually is a 'param' part in the url
if (!isset($param)) {
    $param = '';
}
$tabPath = explode('/', trim($param, '/'));

$isAjax = false;
if (current($tabPath) . '/' === TAB_AJAX_CONTENT_PREFIX) {
    $isAjax = true;
    array_shift($tabPath);
}
define('IS_AJAX', $isAjax);

$selectedtab = !empty($tabPath[0]) ? $tabPath[0] : SELECTED_TAB_ENTITIES;
$selectedSubTab = !empty($tabPath[1]) ? $tabPath[0] .'-' . $tabPath[1] : null;

$msg = (isset($_REQUEST['msg']) && !empty($_REQUEST['msg'])) ? $_REQUEST['msg'] : null;



/* START TAB ADMIN POST HANDLER ***************************************************************************************/
if(isset($_POST['add_usersubmit'])) {
    $selectedtab = SELECTED_TAB_ADMIN;
    if (empty($_POST['userid']) || empty($_POST['type'])) {
        $msg = 'error_user_not_created_due_params';
    } else {
        $check_user = new sspmod_janus_User($janus_config->getValue('store'));
        $check_user->setUserid($_POST['userid']);
 
        if ($check_user->load(sspmod_janus_User::USERID_LOAD) != FALSE) {
            $msg = 'error_user_already_exists';
        } else {
            $new_user = new sspmod_janus_User($janus_config->getValue('store'));
            $new_user->setUserid($_POST['userid']);
            $new_user->setType($_POST['type']);
            if(isset($_POST['active']) && $_POST['active'] == 'on') {
                $active = 'yes';
            } else {
                $active = 'no';
            }
            $new_user->setActive($active);
            $new_user->setData($_POST['userdata']);
            if(!$new_user->save()) {
                $msg = 'error_user_not_created';
            } else {
                redirect(
                    SimpleSAML_Utilities::selfURLNoQuery(),
                    array(),
                    IS_AJAX
               );
            }
        }
    }
}
/* END TAB ADMIN POST HANDLER *****************************************************************************************/



/* START ENTITIES POST HANDLER ****************************************************************************************/
if(isset($_POST['submit'])) {
    $selectedtab = SELECTED_TAB_ENTITIES;
    if (!empty($_POST['entityid'])) {
        if (check_uri($_POST['entityid']) || true) {  # HACK: For SURFnetGuests : https://espee-test.surfnet.nl/federate/metadata/saml20/SURFnetGuests
            if(!isset($_POST['entityid']) || empty($_POST['entitytype'])) {
                $msg = 'error_no_type';
                $old_entityid = $_POST['entityid'];
                $old_entitytype = $_POST['entitytype'];
            } else {
                $msg = $mcontrol->createNewEntity($_POST['entityid'], $_POST['entitytype']);
                if(is_int($msg)) {
                    $entity = new sspmod_janus_Entity($janus_config);
                    $pm->subscribe($user->getUid(), 'ENTITYUPDATE-'. $msg);
                    $directlink = SimpleSAML_Module::getModuleURL('janus/editentity.php', array('eid' => $msg));
                    $pm->post(
                        'New entity created',
                        'Permalink: <a href="' . $directlink . '">' . $directlink . '</a><br /><br />A new entity has been created.<br />Entityid: '. $_POST['entityid']. '<br />Entity type: '.$_POST['entitytype'],
                        'ENTITYCREATE',
                        $user->getUid()
                    );
                    redirect(
                        SimpleSAML_Module::getModuleURL('janus/editentity.php'),
                        array('eid' => $msg),
                        IS_AJAX
                    );
                }
            }
        } else {
            $msg = 'error_entity_not_url';
            $old_entityid = $_POST['entityid'];
            $old_entitytype = $_POST['entitytype'];
        }
    } else if (!empty($_POST['metadata_xml'])) {
        $doc = new DOMDocument();
        $doc->loadXML($_POST['metadata_xml']);
        
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        
        $query = '/md:EntityDescriptor';
        $entity = $xpath->query($query);
        $entityid = $entity->item(0)->getAttribute('entityID');

        $query = '/md:EntityDescriptor/md:SPSSODescriptor';
        $sp = $xpath->query($query);

        if($sp->length > 0) {
            $type = 'saml20-sp';
        }
        
        $query = '/md:EntityDescriptor/md:IDPSSODescriptor';
        $idp = $xpath->query($query);

        if($idp->length > 0) {
            $type = 'saml20-idp';
        }

        $msg = $mcontrol->createNewEntity($entityid, $type);
        if(is_int($msg)) {
            $econtroller = new sspmod_janus_EntityController($janus_config);
            $econtroller->setEntity((string) $msg);
            $econtroller->loadEntity();

            $pm->subscribe($user->getUid(), 'ENTITYUPDATE-'. $msg);
            $directlink = SimpleSAML_Module::getModuleURL('janus/editentity.php', array('eid' => $msg));
            $pm->post(
                'New entity created',
                'Permalink: <a href="' . $directlink . '">' . $directlink . '</a><br /><br />A new entity has been created.<br />Entityid: '. $_POST['entityid']. '<br />Entity type: ' . $_POST['entitytype'],
                'ENTITYCREATE',
                $user->getUid()
            );

            $msg = 'text_entity_created';
            
            if($type == 'saml20-sp') {
                $msg = $econtroller->importMetadata20SP($_POST['metadata_xml'], $update);
            } else if($type == 'saml20-idp') {
                $msg = $econtroller->importMetadata20IdP($_POST['metadata_xml'], $update);
            } else {
                $msg = 'error_metadata_not_import';    
            }

            $econtroller->saveEntity();

            redirect(
                SimpleSAML_Utilities::selfURLNoQuery(), 
                Array(
                    'msg' => $msg
                ),
                IS_AJAX
            );
        }
    } else {
        $msg = 'error_entity_not_url';
        $old_entityid = $_POST['entityid'];
        $old_entitytype = $_POST['entitytype'];
    }
}
/* END TAB ENTITIES POST HANDLER **************************************************************************************/



/* START TAB USERDATA POST HANDLER ************************************************************************************/
if(isset($_POST['usersubmit'])) {
    $selectedtab = SELECTED_TAB_USERDATA;
    $user->setData($_POST['userdata']);
    $user->setEmail($_POST['user_email']);
    $user->setSecret($_POST['user_secret']);
    $user->save();
    $pm->post(
        'Userinfo update',
        'User info updated:<br /><br />' . $_POST['userdata'] . '<br /><br />E-mail: ' . $_POST['user_email'],
        'USER-' . $user->getUid(),
        $user->getUid());
    
    redirect(
        SimpleSAML_Utilities::selfURLNoQuery(), 
        Array(),
        IS_AJAX
    );
}
/* END TAB USERDATA POST HANDLER **************************************************************************************/



/* START TAB ARPADMIN POST HANDLER ************************************************************************************/
if (isset($_POST['arp_delete'])) {
    $selectedtab = SELECTED_TAB_ARPADMIN;
    $arp = new sspmod_janus_ARP();
    $arp->setAid((int)$_POST['arp_delete']);
    $arp->delete();
}

if (isset($_POST['arp_edit'])) {
    $selectedtab = SELECTED_TAB_ARPADMIN;
    $arp = new sspmod_janus_ARP();
    if (isset($_POST['arp_id'])) {
        $arp->setAid((int)$_POST['arp_id']);
    }
    if (isset($_POST['arp_name'])) {
        $arp->setName($_POST['arp_name']);
    }
    if (isset($_POST['arp_description'])) {
        $arp->setDescription($_POST['arp_description']);
    }
    if (isset($_POST['arp_is_default'])) {
        $arp->setDefault();
    }
    if (isset($_POST['arp_attributes'])) {
        $arp->setAttributes($_POST['arp_attributes']);
    }

    $arp->save();
}
/* END TAB ARPADMIN POST HANDLER **************************************************************************************/



/* START TAB MESSAGE PROVISIONING *************************************************************************************/
if($selectedtab == SELECTED_TAB_MESSAGE) {
$subscriptions = $pm->getSubscriptions($user->getUid());
$subscriptionList = $pm->getSubscriptionList();

if(isset($_GET['page'])) {
    $page = $_GET['page'];
    $messages = $pm->getMessages($user->getUid(), $page);
} else {
    $page = 1;
    $messages = $pm->getMessages($user->getUid());
}
$messages_total = $pm->countMessages($user->getUid());
}
/* END TAB MESSAGE PROVISIONG *****************************************************************************************/



/* START TAB ENTITIES PROVISIONING ************************************************************************************/
if($selectedtab == SELECTED_TAB_ENTITIES) {
// Entity filter
$entity_filter = null;
$entity_filter_exclude = null;
if(isset($_GET['entity_filter']) && $_GET['entity_filter'] != 'nofilter') {
    $entity_filter = $_GET['entity_filter'];
}
if(isset($_GET['entity_filter_exclude']) && $_GET['entity_filter_exclude'] != 'noexclude') {
    $entity_filter_exclude = $_GET['entity_filter_exclude'];
}
}
/* END TAB ENTITIES PROVISIONING **************************************************************************************/



/* START TAB ARPADMIN PROVISIONING ************************************************************************************/
if ($selectedtab == SELECTED_TAB_ARPADMIN) {
// Convert legacy attribute specification to new style (< v.1.11)
$arp_attributes = array();
$old_arp_attributes = $janus_config->getValue('attributes');
foreach ($old_arp_attributes as $label => $arp_attribute) {
    if (is_array($arp_attribute)) {
        $arp_attributes[$label] = $arp_attribute;
    }
    else {
        $arp_attributes[$arp_attribute] = array('name' => $arp_attribute);
    }
}
}
/* END TAB ARPADMIN PROVISIONING **************************************************************************************/



$et = new SimpleSAML_XHTML_Template($config, 'janus:dashboard.php', 'janus:dashboard');
$et->data['header'] = 'JANUS';
$et->data['selectedtab'] = $selectedtab;
$et->data['selectedSubTab'] = $selectedSubTab;



/* START TAB ARPADMIN PROVISIONING ************************************************************************************/
if($selectedtab == SELECTED_TAB_ARPADMIN
    || $selectedSubTab == SELECTED_SUBTAB_ADMIN_ENTITIES) {
$et->data['adminentities'] = $mcontrol->getEntities(true);
}
/* END TAB ARPADMIN PROVISIONING **************************************************************************************/



/* START TAB ENTITIES PROVISIONING ************************************************************************************/
if($selectedtab == SELECTED_TAB_ENTITIES) {
if(isset($_GET['submit_search']) && !empty($_GET['q'])) {
    $et->data['entities'] = $mcontrol->searchEntities($_GET['q'], $entity_filter, $entity_filter_exclude, isset($_GET['sort']) ? $_GET['sort'] : null, isset($_GET['order']) ? $_GET['order'] : null);
}else {
    $et->data['entities'] = $mcontrol->getEntities(false, $entity_filter, $entity_filter_exclude, isset($_GET['sort']) ? $_GET['sort'] : null, isset($_GET['order']) ? $_GET['order'] : null);
}
$et->data['entity_filter'] = $entity_filter;
$et->data['entity_filter_exclude'] = $entity_filter_exclude;
$et->data['query'] = isset($_GET['q']) ? $_GET['q'] : '';
$et->data['order'] = isset($_GET['order']) ? $_GET['order'] : null;
$et->data['sort'] = isset($_GET['sort']) ? $_GET['sort'] : null;
$et->data['is_searching'] = !empty($et->data['order']) ||
                            !empty($et->data['sort']) ||
                            !empty($et->data['query']) ||
                            !empty($et->data['entity_filter']) ||
                            !empty($et->data['entity_filter_exclude']);
}
/* END TAB ENTITIES PROVISIONING **************************************************************************************/



// User is needed by all pages
$et->data['userid'] = $userid;
$et->data['user'] = $mcontrol->getUser();
$et->data['uiguard'] = new sspmod_janus_UIguard($janus_config->getValue('access'));



/* START TAB MESSAGE PROVISIONING *************************************************************************************/
if($selectedtab == SELECTED_TAB_MESSAGE) {
$et->data['user_type'] = $user->getType();
$et->data['subscriptions'] = $subscriptions;
$et->data['subscriptionList'] = $subscriptionList;
$et->data['messages'] = $messages;
$et->data['messages_total'] = $messages_total;
$et->data['external_messengers'] = $janus_config->getArray('messenger.external');
$et->data['current_page'] = $page;
$et->data['last_page'] = ceil((float)$messages_total / $pm->getPaginationCount());
}
/* END TAB MESSAGE PROVISIONING ***************************************************************************************/



$et->data['logouturl'] = SimpleSAML_Module::getModuleURL('core/authenticate.php') . '?logout';


/* START TAB ARPADMIN PROVISIONING ************************************************************************************/
if ($selectedtab == SELECTED_TAB_ARPADMIN) {
$et->data['arp_attributes'] = $arp_attributes;
}
/* END TAB ARPADMIN PROVISIONING **************************************************************************************/



/* START TAB ADMIN PROVISIONING ***************************************************************************************/
if ($selectedtab == SELECTED_TAB_ADMIN) {
$et->data['users'] = $mcontrol->getUsers();
}
/* END TAB ADMIN PROVISIONING *****************************************************************************************/




/* START TAB ENTITIES PROVISIONING ************************************************************************************/
if ($selectedtab == SELECTED_TAB_ENTITIES) {
if(isset($old_entityid)) {
    $et->data['old_entityid'] = $old_entityid;
}
if(isset($old_entitytype)) {
    $et->data['old_entitytype'] = $old_entitytype;
}
}
/* END TAB ENTITIES PROVISIONING **************************************************************************************/


if(isset($msg)) {
    $et->data['msg'] = $msg;
}
$et->show();
?>
