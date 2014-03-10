<?php
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');
$defaultusertype = $janus_config->getValue('defaultusertype', 'technical');

if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

$econtroller = sspmod_janus_DiContainer::getInstance()->getUserController();

$usertypes = $janus_config->getValue('usertypes');

$et = new SimpleSAML_XHTML_Template($config, 'janus:newuser.php', 'janus:newuser');

if(isset($_POST['submit'])) {
    $user = new sspmod_janus_User($janus_config->getValue('store'));
    $user->setUserid($userid);
    $user->setType($defaultusertype);
    $user->setActive('yes');
    $user->save();
    $et->data['user_created'] = TRUE ;
    $pm = new sspmod_janus_Postman();
    $pm->post('New user created', 'A new user has been created with username: '. $user->getUserid(), 'USER-NEW', $user->getUid());
}

$et->data['userid'] = $userid;

$et->show();
?>
