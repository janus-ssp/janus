<?php
/**
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
 */
$session = SimpleSAML_Session::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
if (!$session->isValid($authsource)) {
    echo json_encode(array("status" => "error_no_session"));
    die();
}

if(isset($_POST)) {
    //Handle requests

    $result = array();
    if(!isset($_POST['func'])) {
        $result['status'] = 'error_no_func';
    } else {
        // TO-DO do some stuff
        $function_name = $_POST['func'];
        $params = $_POST;

        // Make function call
        $return = $function_name($params);

        // Did function return a result
        if($return) {
            if(is_array($return)) {
                $result = array_merge($result, $return);
            }
            $result['status'] = 'success';
        } else {
            $result['status'] = 'error_func_call';
        }

    }

    // Send back result
    echo json_encode($result);
} else if(isset($_GET)) {
    // Handle GET requests
}

function markAsRead($params) {
    if(!isset($params['mid'])) {
        return FALSE;
    }

    $pm = new sspmod_janus_Postman();
    $return = $pm->MarkAsRead($params['mid']);

    return $return;
}

function getMessage($params) {
    if(!isset($params['mid'])) {
        return FALSE;
    }

    $pm = new sspmod_janus_Postman();
    $message = $pm->getMessage($params['mid']);
    $return = $message['message'];

    return array('data' => $return);
}

function deleteSubscription($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }
    if(!isset($params['subscription'])) {
        return FALSE;
    }

    $pm = new sspmod_janus_Postman();
    $return = $pm->unSubscribe($params['uid'], $params['subscription']);

    return $return;
}

function addSubscription($params) {
    if(!isset($params['uid'])) {
        return FALSE;
    }
    if(!isset($params['subscription'])) {
        return FALSE;
    }

    $pm = new sspmod_janus_Postman();
    $return = $pm->subscribe($params['uid'], $params['subscription']);

    return $return;
}

function deleteUser($params) {
    if(!isset($params['uid'])) {
        return FALSE;
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
        return FALSE;
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
    return TRUE;
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
?>
