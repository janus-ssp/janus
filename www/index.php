<?php
/*
 * JANUS
 */

$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

// Error loggin in has happend
if(isset($_GET['error'])) {
    $et = new SimpleSAML_XHTML_Template($config, 'janus:error.php', 'janus:error');
    $et->data['header'] = 'JANUS';
    if($_GET['error'] == 'error_index_user_inactive') {
        $et->data['error'] = 'error_user_inactive';
    }
    else {
        $et->data['error'] = 'error_auth_user';
    }
    $et->show();
    exit();
}

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    $returnURL = $session->getData('string', 'refURL');

    if (is_null($returnURL)) {
        $returnURL = SimpleSAML_Utilities::selfURL();
    } else {
        $session->deleteData('string' ,'refURL');
    }
    
    SimpleSAML_Auth_Default::initLogin(
        $authsource,
        $returnURL,
        NULL,
        $_GET
    );
}

$user = new sspmod_janus_User($janus_config->getValue('store'));
$user->setUserid($userid);

if(!$user->load(sspmod_janus_User::USERID_LOAD)) {
    $autocreatenewuser = $janus_config->getValue('user.autocreate', false);
    if($autocreatenewuser) {
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/newUser.php'), array('userid' => $userid));
    } else {
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/noNewUser.php'), array('userid' => $userid));
    }
} else {
    if ($user->getActive() === 'yes') {
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/dashboard.php/entities'));
    } else {
        $session->doLogout();
        SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php?error=error_index_user_inactive'));
    }
}
?>
