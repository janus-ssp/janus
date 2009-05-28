<?php
$config = SimpleSAML_Configuration::getInstance();

if (!array_key_exists('AuthState', $_REQUEST)) {
		throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['AuthState'];

/* Retrieve the authentication state. */
$state = SimpleSAML_Auth_State::loadState($authStateId, sspmod_janus_Auth_Source_MailToken::STAGEID);

if(array_key_exists('SPMetadata', $state)) {
	if(array_key_exists('token', $state['SPMetadata']) && array_key_exists('mail', $state['SPMetadata'])) {
		$token = $state['SPMetadata']['token'];
		$mail = $state['SPMetadata']['mail'];
		if(array_key_exists('mail', $_REQUEST)) {
			$mail = $_REQUEST['mail'];
		}
		$error = sspmod_janus_Auth_Source_MailToken::handleLogin($authStateId, $mail, $token);
	}
}

//echo "<pre>";
//var_dump($state);
//echo "</pre>";

$t = new SimpleSAML_XHTML_Template($config, 'janus:mailtoken.php', 'janus:mailtoken');

$t->data['stateparams'] = array('AuthState' => $authStateId);
$t->data['header'] = 'JANUS - Login';	
if (isset($error)) {
	$t->data['msg'] = $error;
}

$t->show();


?>
