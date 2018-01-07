<?php

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

require __DIR__ . '/_includes.php';

$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();
$authsource = $janus_config->getValue('auth', 'login-admin');

$as = new \SimpleSAML\Auth\Simple($authsource);

if (!$as->isAuthenticated()) {
    echo json_encode(array("status" => "error_no_session"));
    throw new SimpleSAML_Error_Exception('No valid session');
}

$ALLOWED_FUNCTIONS = array(
    'uploadFile',
    'getARP',
    'validateMetadataField',
    'markAsRead',
    'getMessageList',
    'getMessage',
    'deleteSubscription',
    'addSubscription',
    'updateSubscription',
    'deleteUser',
    'editUser',
    'getEntityUsers',
    'getNonEntityUsers',
    'removeUserFromEntity',
    'addUserToEntity',
    'deleteEntity',
    'disableEntity',
    'enableEntity',
);

if(!isset($_POST)) {
    header('400 Bad Request');
    die(json_encode(array('status'=>'error_no_post')));
}

if(!isset($_POST['func'])) {
    header('400 Bad Request');
    die(json_encode(array('status'=>'error_no_func')));
}

$function_name = (string)$_POST['func'];

if (!in_array($function_name, $ALLOWED_FUNCTIONS)) {
    header('400 Bad Request');
    die(json_encode(array('status'=>'error_disallowed_func')));
}

$csrf_provider = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();
if (!isset($_POST['csrf_token']) || !$csrf_provider->isCsrfTokenValid('ajax', $_POST['csrf_token'])) {
    header('400 Bad Request');
    SimpleSAML_Logger::warning('Janus: [SECURITY] CSRF token not found or invalid');
    die(json_encode(array('status'=>'error_csrf')));
}

$user = getUser($as, $janus_config);
$securityContext = sspmod_janus_DiContainer::getInstance()->getSecurityContext();

// ??? is 'allentities' the right permission for enabling superuser status ???
$superuser = $securityContext->isGranted('allentities');

// if (isset($params['uid']) && !$superuser) { $params['uid'] = $user->getUid(); }
// Gross hack - sometimes we need to check the permissions in situ
// therefore we put $user and $guard into $params with special names ...
$_POST['__uid'] = $user->getUid();
$_POST['__userid'] = $user->getUserid();
$_POST['__superuser'] = $superuser;

// Check that user is allowed to touch entity
if (isset($_POST['eid'])) {
    checkEntityPermission($janus_config, $_POST);
}

// a non superuser may only use ENTITYUPDATE-<eid> - check for allowed eid here
if (isset($_POST['subscription']) && !$superuser) {
    if (!preg_match('/^ENTITYUPDATE-(\d+)$/', $_POST['subscription'], $matched)) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
    $_POST['eid'] = $matched[1];
    checkEntityPermission($janus_config, $_POST);
}

// Make function call
// other checks are done in each function ...
$return = $function_name($_POST);

$result = array();
// Did function return a result
if ($return) {
    if(is_array($return)) {
        $result = array_merge($result, $return);
    }
    if(!isset($result['status'])) {
        $result['status'] = 'success';
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    $result['status'] = 'error_func_call';
}

echo json_encode($result);


function getUser(\SimpleSAML\Auth\Simple $as, ConfigProxy $janus_config)
{
    // Get data from config
    /** @var string $useridattr */
    $useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

    // Validate user
    $attributes = $as->getAttributes();

    // Check if userid exists
    if (!isset($attributes[$useridattr])) {
        echo json_encode(array('status' => 'user_id_is_missing')); exit;
    }

    $userid = $attributes[$useridattr][0];

    $user = new sspmod_janus_User();
    $user->setUserid($userid);
    $user->load(sspmod_janus_User::USERID_LOAD);
    return $user;
}

// Checking of entity permissions is done before calling the function
// other checks are done in the each function
function checkEntityPermission($janus_config, $params)
{
    // now check entity
    $mcontroller = sspmod_janus_DiContainer::getInstance()->getEntityController();

    if(!$entity = $mcontroller->setEntity($params['eid'])) {
        echo json_encode(array('status' => 'error_in_setEntity')); exit;
    }

    $mcontroller->loadEntity();

    $allowedUsers = $mcontroller->getUsers();
    if(!(array_key_exists($params['__userid'], $allowedUsers) || $params['__superuser'])) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
}

function getARP($params) {
    if(!isset($params['aid'])) {
        return false;
    }

    $arp = new sspmod_janus_ARP();
    $arp->setAid((int)$params['aid']);
    $arp->load();

    $attributes = $arp->getAttributes();
    if(empty($attributes)) {
        $attributes = array();
    }
    ksort($attributes);
    return array(
        'aid'           => $arp->getAid(),
        'name'          => $arp->getName(),
        'description'   => $arp->getDescription(),
        'is_default'    => $arp->isDefault(),
        'attributes'    => $attributes
    );
}

function validateMetadataField($params) {
    if(!isset($params['userfunc'])) {
        return false;
    }
    include(dirname(dirname(__FILE__)) . '/lib/Validation/Metadata.php');
    if(!isset($functions[$params['userfunc']])) {
        return array('status' => 'no_such_user_func');
    }
    $function = create_function('$value', $functions[$params['userfunc']]['code']);
    $return = $function($params['value']); 
    return array('valid' => $return);
}

function markAsRead($params) {
    if(!isset($params['mid'])) {
        return FALSE;
    }

    // getMessage does access control and sends 'permission_denied' directly to client ...
    
    getMessage($params);
    $pm = new sspmod_janus_Postman();
    $return = $pm->MarkAsRead($params['mid']);

    return $return;
}

function getMessageList($params) {

    // only a superuser can get a messagelist for other users
    if ($params['uid'] != $params['__uid'] && !$params['__superuser']) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
    
    $uid = $params['uid'];
    $page = $params['page'];
    $pm = new sspmod_janus_Postman();
    $output = array();
    $messages = $pm->getMessages($uid, $page);
    if(empty($messages)) {
        $output[] = 'Empty';
    } else {
        foreach($messages AS $message) {
            if($message['read'] == 'no') {
                $output[] = '<div style="border-bottom: 1px solid #AAAAAA;">';
                $output[] = '<input type="checkbox" name="message_cb[]" value="message_cb-'. $message['mid'] .'"> <a id="message-title-'. $message['mid'] .'" style="font-weight: bold;" onclick="openMessage('. $message['mid'] .')">'. date("d/n-Y H:i:s", strtotime($message['created'])) .' - '. $message['subject'] .'</a>';
                $output[] = '</div>';
            } else {
                $output[] = '<div style="border-bottom: 1px solid #AAAAAA;">';
                $output[] = '<input type="checkbox" name="message_cb[]" value="message_cb-'. $message['mid'] .'"> <a id="message-title-'. $message['mid'] .'" onclick="openMessage('. $message['mid'] .')">'. date("d/n-Y H:i:s", strtotime($message['created'])) .' - '. $message['subject'] .'</a>';
                $output[] = '</div>';
            }
            $output[] = '<div id="message-'. $message['mid'] .'" class="dashboard_inbox_message_desc"></div>';
        }
    }

    return array('data' => join(' ', $output),
                 'page' => $page,
                 'status' => 'success');
}

function getMessage($params) {
    if(!isset($params['mid'])) {
        return FALSE;
    }
    
    $janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

    $pm = new sspmod_janus_Postman();
    $message = $pm->getMessage($params['mid']);
    
    if ($message['uid'] != $params['__uid'] && !$params['__superuser']) {
            echo json_encode(array('status' => 'permission_denied')); exit;
    }

    $user = new sspmod_janus_User();
    $user->setUid($message['from']);
    $user->load();
    
    $return = wordwrap($message['message'], 75, "\n", TRUE);

    return array(
        'data' => $return,
        'from' => htmlspecialchars($user->getUserid()),
        'address' => htmlspecialchars($message['subscription']),
    );
}

function deleteSubscription($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }
    if(!isset($params['sid'])) {
        return FALSE;
    }

    // only the user herself can delete a subscription
    if ($params['uid'] != $params['__uid']) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }

    $pm = new sspmod_janus_Postman();
    $return = $pm->unSubscribe($params['uid'], $params['sid']);

    return $return;
}

function addSubscription($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }
    if(!isset($params['subscription'])) {
        return FALSE;
    }

    // only the user herself can make a new subscription
    if ($params['uid'] != $params['__uid']) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }

    $pm = new sspmod_janus_Postman();
    $return = $pm->subscribe($params['uid'], $params['subscription']);
    
    if($return === false) {
        return array('status' => 'User is already subscribing to that address');
    }

    return array('sid' => $return);
}

function updateSubscription($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }
    if(!isset($params['sid'])) {
        return FALSE;
    }
    if(!isset($params['type'])) {
        return FALSE;
    }

    // only the user herself can update a subscription
    if ($params['uid'] != $params['__uid']) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }

    // check for user only updating her own subscriptions is in $pm->updateSubscription
    $pm = new sspmod_janus_Postman();
    $return = $pm->updateSubscription($params['sid'], $params['uid'], $params['type']);

    return $return;
}

function editUser($params) {
    if(empty($params['uid']) || empty($params['userid']) || !isset($params['active']) || empty($params['type'])) {
        return array('status' => 'missing_param');
    }
    
    if (!$params['__superuser']) { 
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
    
    $janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

    $uid = $params['uid'];

    $user = new sspmod_janus_User();
    $user->setUid($uid);
    $user->load(sspmod_janus_User::UID_LOAD);
    $user->setActive($params['active']);
    $user->setUserid($params['userid']);
    $user->setType($params['type']);
    $user->save();
    return array('uid' => $uid);
}

function getEntityUsers($params) {
    if(!isset($params['eid'])) {
        return FALSE;
    }

    $eid = $params['eid'];

    $util = new sspmod_janus_AdminUtil();
    $users = $util->hasAccess($eid);

    $return = array();
    foreach($users AS $user) {
        $return[] = array('optionValue' => $user['uid'], 'optionDisplay' => $user['userid']);
    }

    usort($return, function($a, $b){
        return strcasecmp($a['optionDisplay'], $b['optionDisplay']);
    });

    return array('data' => $return);
}

function getNonEntityUsers($params) {
    if(!isset($params['eid'])) {
        return FALSE;
    }

    $eid = $params['eid'];

    $util = new sspmod_janus_AdminUtil();
    $users = $util->hasNoAccess($eid);

    $return = array();
    foreach($users AS $user) {
        $return[] = array('optionValue' => $user['uid'], 'optionDisplay' => $user['userid']);
    }
    
    usort($return, function($a, $b){
        return strcasecmp($a['optionDisplay'], $b['optionDisplay']);
    });
    
    return array('data' => $return);
}

function removeUserFromEntity($params) {
    if(!isset($params['eid']) || !isset($params['uid'])) {
        return FALSE;
    }

    $eid = $params['eid'];
    $uid = $params['uid'];

    $util = new sspmod_janus_AdminUtil();
    if(!$util->removeUserFromEntity($eid, $uid)) {
        return FALSE;
    }
    return array('eid' => $eid, 'uid' => $uid);
}

function addUserToEntity($params) {
    if(!isset($params['eid']) || !isset($params['uid'])) {
        return FALSE;
    }

    $eid = $params['eid'];
    $uid = $params['uid'];

    # security hack - uid is actually userid ie. user@example.com - convert it to a janus uid as expected for further processing
    $janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();
    $user = new sspmod_janus_User();

    $user->setUserid($uid);
    if ($user->load(sspmod_janus_User::USERID_LOAD) === false) { echo json_encode(array('status' => 'Unknown user')); exit; }
    $actual_uid = $user->getUid();
    
    $util = new sspmod_janus_AdminUtil();
    try {
        if(!$userid = $util->addUserToEntity($eid, $actual_uid)) {
            return FALSE;
        }
    } catch (Exception $e) {
        echo json_encode(array('status' => 'An unspecified error occurred')); exit; 
    }
    return array('eid' => $eid, 'uid' => $actual_uid, 'userid' => $userid);
}

function deleteEntity($params)
{
    if(!isset($params['eid'])) {
        return FALSE;
    }

    $eid = $params['eid'];

    $util = new sspmod_janus_AdminUtil();
    $util->deleteEntity($eid);

    return array('eid' => $eid);
}

function disableEntity($params)
{
    if(!isset($params['eid'])) {
        return FALSE;
    }

    $eid = $params['eid'];

    $util = new sspmod_janus_AdminUtil();
    $util->disableEntity($eid);

    return array('eid' => $eid);
}

function enableEntity($params)
{
    if(!isset($params['eid'])) {
        return FALSE;
    }

    $eid = $params['eid'];

    $util = new sspmod_janus_AdminUtil();
    $util->enableEntity($eid);

    return array('eid' => $eid);
}
