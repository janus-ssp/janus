<?php

$session        = SimpleSAML_Session::getInstance();
$sspConfig      = SimpleSAML_Configuration::getInstance();
$janusConfig    = sspmod_janus_DiContainer::getInstance()->getConfig();

if (!$janusConfig->getValue('user.autocreate', false)) {
    throw new SimpleSAML_Error_Error("User autocreation disabled.");
}

/** @var string $authenticationSource */
$authenticationSource   = $janusConfig->getValue('auth'           , 'login-admin');
/** @var string $userIdAttribute */
$userIdAttribute        = $janusConfig->getValue('useridattr'     , 'eduPersonPrincipalName');
/** @var string $defaultUserType */
$defaultUserType        = $janusConfig->getValue('defaultusertype', 'technical');

// Require a authenticated user.
if (!$session->isValid($authenticationSource)) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
    exit;
}
$attributes = $session->getAttributes();

// Require that we can get this users id.
if (!isset($attributes[$userIdAttribute])) {
    throw new Exception('User ID is missing');
}
/** @var string $userId */
$userId = $attributes[$userIdAttribute][0];

if (isset($_POST['submit'])) {
    // Create the user
    $user = new sspmod_janus_User($janusConfig->getValue('store'));
    $user->setUserid($userId);
    $user->setType($defaultUserType);
    $user->setActive('yes');
    $user->save();

    // Trigger an event
    $pm = new sspmod_janus_Postman();
    $pm->post(
        'New user created',
        'A new user has been created with username: '. $user->getUserid(),
        'USER-NEW', $user->getUid()
    );
}

$template = new SimpleSAML_XHTML_Template($sspConfig, 'janus:newuser.php', 'janus:newuser');
$template->data['userid'] = $userId;
if (isset($user)) {
    $template->data['user_created'] = TRUE;
}
$template->show();