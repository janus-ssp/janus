<?php

$session = SimpleSAML_Session::getInstance();

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

	$config = SimpleSAML_Configuration::getInstance();
	$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

	$uid = $params['uid'];

	$user = new sspmod_janus_User($janus_config->getValue('store'));
	$user->setUid($uid);
	$user->load(sspmod_janus_User::UID_LOAD);
	$user->setActive('no');
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
		$return[] = array('optionValue' => $user['uid'], 'optionDisplay' => $user['email']);
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
		$return[] = array('optionValue' => $user['uid'], 'optionDisplay' => $user['email']);
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
	if(!$email = $util->addUserToEntity($eid, $uid)) {
		return FALSE;
	}
	return array('eid' => $eid, 'uid' => $uid, 'email' => $email);
}
?>
