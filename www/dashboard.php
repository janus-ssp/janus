<?php
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');


if ($session->isValid($authsource)) {
	$attributes = $session->getAttributes();
	// Check if userid exists
	if (!isset($attributes[$useridattr])) 
		throw new Exception('User ID is missing');
	$userid = $attributes[$useridattr][0];
} else {
	SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}


$mcontrol = new sspmod_janus_UserController($janus_config);
$pm = new sspmod_janus_Postman();

if(!$user = $mcontrol->setUser($userid)) {
	die('Error in setUser');
}

if(isset($_POST['submit'])) {
    if (filter_var($_POST['entityid'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
        if(!isset($_POST['entityid']) || empty($_POST['entitytype'])) {
            $msg = 'error_no_type';
            $old_entityid = $_POST['entityid'];
        } else {
            $msg = $mcontrol->createNewEntity($_POST['entityid'], $_POST['entitytype']);
            if(is_int($msg)) {
                $entity = new sspmod_janus_Entity($janus_config);
                $pm->subscribe($user->getUid(), 'ENTITYUPDATE-'. $msg);
                $pm->post(
                    'New entity created', 
                    "A new entity has been created.<br />Entityid: ". $_POST['entityid']. "<br />Entity type: ".$_POST['entitytype'], 
                    'ENTITYCREATE', 
                    $user->getUid()
                );
                $msg = 'text_entity_created';
            }
        }
    } else {
        $msg = 'error_entity_not_url';
        $old_entityid = $_POST['entityid'];
    }
}

if(isset($_POST['usersubmit'])) {
    $user->setData($_POST['userdata']);
    $user->save();
}

$subscriptions = $pm->getSubscriptions($user->getUid());
$subscriptionList = $pm->getSubscriptionList();
$messages = $pm->getMessages($user->getUid());

$et = new SimpleSAML_XHTML_Template($config, 'janus:dashboard.php', 'janus:janus');
$et->data['header'] = 'JANUS';
$et->data['entities'] = $mcontrol->getEntities();
$et->data['userid'] = $userid;
$et->data['user'] = $mcontrol->getUser();
$et->data['uiguard'] = new sspmod_janus_UIguard($janus_config->getValue('access'));
$et->data['user_type'] = $user->getType();
$et->data['subscriptions'] = $subscriptions;
$et->data['subscriptionList'] = $subscriptionList;
$et->data['messages'] = $messages;

$et->data['users'] = $mcontrol->getUsers();

if(isset($old_entityid)) {
	$et->data['old_entityid'] = $old_entityid;
}
if(isset($msg)) {
	$et->data['msg'] = $msg;
}

$et->show();

?>
