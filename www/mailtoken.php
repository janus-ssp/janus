<?php
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');


if (!array_key_exists('AuthState', $_REQUEST)) {
        throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['AuthState'];

/* Retrieve the authentication state. */
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_janus_Auth_Source_MailToken::STAGEID);

$mail = null;
$token = null;

if(array_key_exists('mail', $_REQUEST)) {
    $mail = $_REQUEST['mail'];
}

if(array_key_exists('SPMetadata', $state)) {
    if(array_key_exists('token', $state['SPMetadata'])) {
        $token = $state['SPMetadata']['token'];
    }
    if (array_key_exists('mail', $state['SPMetadata'])) {
        $mail = $state['SPMetadata']['mail'];
    }
}

$error = sspmod_janus_Auth_Source_MailToken::handleLogin($authStateId, $mail, $token);

$adminname = $janus_config->getValue('admin.name', 'No admin configured');
$adminemail = $janus_config->getValue('admin.email', 'No admin email configured');

$t = new SimpleSAML_XHTML_Template($config, 'janus:mailtoken.php', 'janus:mailtoken');

$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['header'] = 'JANUS - Login';
$t->data['adminname']= $adminname;
$t->data['adminemail']= $adminemail;
$t->data['mail'] = $mail;
if (isset($error)) {
    $t->data['msg'] = $error;
}

$t->show();

?>
