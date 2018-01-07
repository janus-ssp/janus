<?php

require __DIR__ . '/_includes.php';

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

$as = new \SimpleSAML\Auth\Simple($authenticationSource);

// Require a authenticated user.
if (!$as->isAuthenticated()) {
    SimpleSAML_Utilities::redirectTrustedUrl(\SimpleSAML\Module::getModuleURL('janus/index.php'));
    exit;
}
$attributes = $as->getAttributes();

// Require that we can get this users id.
if (!isset($attributes[$userIdAttribute])) {
    throw new Exception('User ID is missing');
}
/** @var string $userId */
$userId = $attributes[$userIdAttribute][0];

if (isset($_POST['submit'])) {
    $csrf_provider = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();
    if (!isset($_POST['csrf_token']) || !$csrf_provider->isCsrfTokenValid('add_user', $_POST['csrf_token'])) {
        SimpleSAML_Logger::warning('Janus: [SECURITY] CSRF token not found or invalid');
        throw new SimpleSAML_Error_BadRequest('Missing valid csrf token!');
    }
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
        'A new user has been created with username: '. htmlspecialchars($user->getUserid()),
        'USER-NEW',
        $user->getUid()
    );
}

$template = new SimpleSAML_XHTML_Template($sspConfig, 'janus:newuser.php', 'janus:newuser');
$template->data['userid'] = $userId;
if (isset($user)) {
    $template->data['user_created'] = TRUE;
}
$template->show();
