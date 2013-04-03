<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */

// Ses session when using Flash to do file upload
// Should be removed when bug in Flash player is fixed
// Set cookie as SSP uses a cookie for retrieving authentication

if (isset($_POST["SimpleSAMLAuthToken"])) {
    $_COOKIE['SimpleSAMLAuthToken'] = $_POST['SimpleSAMLAuthToken'];
}

if (isset($_POST["PHPSESSID"])) {
    session_id($_POST["PHPSESSID"]);
    session_start();
}

$session = SimpleSAML_Session::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
if (!$session->isValid($authsource)) {
    echo json_encode(array("status" => "error_no_session"));
    throw new SimpleSAML_Error_Exception('No valid session');
}

/**
    20130318 freek@wayf.dk
    Utility functions to help access control
    Adapted from the corresponding check in EditEntity.php ...
*/

function getUser($session, $janus_config)
{
    // Get data from config
    $useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

    // Validate user
    $attributes = $session->getAttributes();

    // Check if userid exists
    if (!isset($attributes[$useridattr])) {
        echo json_encode(array('status' => 'user_id_is_missing')); exit;
    }

    $userid = $attributes[$useridattr][0];

    $user = new sspmod_janus_User($janus_config->getValue('store'));
    $user->setUserid($userid);
    $user->load(sspmod_janus_User::USERID_LOAD);
    return $user;
}

// Checking of entity permissions is done before calling the function
// other checks are done in the each function
function checkEntityPermission($janus_config, $params)
{
    // now check entity
    $mcontroller = new sspmod_janus_EntityController($janus_config);

    if(!$entity = $mcontroller->setEntity($params['eid'])) {
        echo json_encode(array('status' => 'error_in_setEntity')); exit;
    }

    $mcontroller->loadEntity();

    $allowedUsers = $mcontroller->getUsers();
    if(!(array_key_exists($params['__userid'], $allowedUsers) || $params['__superuser'])) {
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
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

if(isset($_POST)) {
    //Handle requests

    $result = array();
    if(!isset($_POST['func'])) {
        $result['status'] = 'error_no_func';
    } else {
        // TO-DO do some stuff
        $function_name = (string)$_POST['func'];
        $params = $_POST;
        $return = null;
        if (in_array($function_name, $ALLOWED_FUNCTIONS)) {
            $user = getUser($session, $janus_config);
            $guard = new sspmod_janus_UIguard($janus_config->getArray('access', array()));

            // ??? is 'allentities' the right permission for enabling superuser status ???
            $superuser = $guard->hasPermission('allentities', null, $user->getType(), TRUE);

            // if (isset($params['uid']) && !$superuser) { $params['uid'] = $user->getUid(); }
            // Gross hack - sometimes we need to check the permissions in situ
            // therefore we put $user and $guard into $params with special names ...
            $params['__uid'] = $user->getUid();
            $params['__userid'] = $user->getUserid();
            $params['__superuser'] = $superuser;
            
            // Check that user is allowed to touch entity
            if (isset($params['eid'])) {
                checkEntityPermission($janus_config, $params);
            }

            // Make function call
            // other checks are done in each function ...      
            $return = $function_name($params);
        }

        // Did function return a result
        if($return) {
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
    }

    // Send back result
    // PHP versions prior to 5.2 don't have json_encode
    echo json_encode($result);
} else if(isset($_GET)) {
    // Handle GET requests
}

function file_upload_error_message($error_code) {
    switch ($error_code) { 
        case UPLOAD_ERR_INI_SIZE: 
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
        case UPLOAD_ERR_FORM_SIZE: 
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
        case UPLOAD_ERR_PARTIAL: 
            return 'The uploaded file was only partially uploaded'; 
        case UPLOAD_ERR_NO_FILE: 
            return 'No file was uploaded'; 
        case UPLOAD_ERR_NO_TMP_DIR: 
            return 'Missing a temporary folder'; 
        case UPLOAD_ERR_CANT_WRITE: 
            return 'Failed to write file to disk'; 
        case UPLOAD_ERR_EXTENSION: 
            return 'File upload stopped by extension'; 
        default: 
            return 'Unknown upload error'; 
    } 
} 

function uploadFile($params) {
    if(!isset($params['eid']))
        return FALSE;   
    
    if(!isset($params['index']))
        return FALSE;   
    
    $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
    $uploaddir = $janus_config->getString('metadatafields.uploadpath') . $params['eid'];
    
    $return = Array();

    if(!file_exists($uploaddir)) {
        if(!@mkdir($uploaddir)) {
            $return['status'] = 'error_noupload';
            $return['error_message'] = 'Could not create upload directory';
        }
    }

    $uploadFileName = time() . '_' . basename($_FILES['Filedata']['name']);
    $uploadfile = $uploaddir . '/' . $uploadFileName;

    if (@move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile)) {
        $return['newfilename'] = $uploadFileName;
        $return['status'] = 'success';
    } else {
        $return['status'] = 'error_noupload';
        $return['error_code'] = $_FILES['Filedata']['error'];
        $return['error_message'] = file_upload_error_message($_FILES['Filedata']['error']);
    }

    $return['index'] = $params['index'];
    
    return $return;            
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
    
    $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

    $pm = new sspmod_janus_Postman();
    $message = $pm->getMessage($params['mid']);
    
    if ($message['uid'] != $params['__uid'] && !$params['__superuser']) {
            echo json_encode(array('status' => 'permission_denied')); exit;
    }

    $user = new sspmod_janus_User($janus_config->getValue('store'));
    $user->setUid($message['from']);
    $user->load();

    $message = strip_tags($message['message'],'<br><a>');
    
    $return = wordwrap($message, 75, "\n", TRUE);

    return array(
        'data' => $return,
        'from' => $user->getUserid(),
        'address' => $message['subscription']
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

    $pm = new sspmod_janus_Postman();
    $return = $pm->updateSubscription($params['sid'], $params['uid'], $params['type']);

    return $return;
}

function deleteUser($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }

    // only the superuser can delete a user and not herself
    if (!$params['__superuser'] || $params['uid'] == $params['__uid']) { 
        echo json_encode(array('status' => 'permission_denied')); exit;
    }

    $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
    $util = new sspmod_janus_AdminUtil();

    $uid = $params['uid'];

    $user = new sspmod_janus_User($janus_config->getValue('store'));
    $user->setUid($uid);
    $user->load();

    $entities = $util->getEntitiesFromUser($uid);

    $sucess = $user->delete();
    if ($sucess) {
        $util = new sspmod_janus_AdminUtil();
        $entity_id_array = array();
        $entity_id_array['eid'] = array();
        foreach($entities as $entity) {
            $entity_id_array['eid'][] = $entity['eid'];
        }
        $util->removeAllEntitiesFromUser($uid);
        return $entity_id_array;
    }
    return FALSE;
}

function editUser($params) {
    if(empty($params['uid']) || empty($params['userid']) || !isset($params['active']) || empty($params['type'])) {
        return array('status' => 'missing_param');
    }
    
    if (!$params['__superuser'] && $params['uid'] != $params['__uid']) { 
        echo json_encode(array('status' => 'permission_denied')); exit;
    }
    
    $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

    $uid = $params['uid'];

    $user = new sspmod_janus_User($janus_config->getValue('store'));
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

    $util = new sspmod_janus_AdminUtil();
    if(!$userid = $util->addUserToEntity($eid, $uid)) {
        return FALSE;
    }
    return array('eid' => $eid, 'uid' => $uid, 'userid' => $userid);
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
?>
