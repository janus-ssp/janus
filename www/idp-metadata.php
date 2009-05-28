<?php
/*
 * Generate IdP metadata 
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package SimpleSAMLphp
 * @subpackeag JANUS
 * @varsion $Id$
 * @TODO Validate that all required metadata fields are present
 */

/* Load simpleSAMLphp, configuration and metadata */
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




$mcontroller = new sspmod_janus_EntityController($janus_config);

$entityid = $_GET['entityid'];
$revisionid = -1;

if(isset($_GET['revisionid'])) {
	$revisionid = $_GET['revisionid'];
}

if($revisionid > -1) {
	if(!$entity = $mcontroller->setEntity($entityid, $revisionid)) {
		die('Error in setEntity');
	}
} else {
	if(!$entity = &$mcontroller->setEntity($entityid)) {
		die('Error in setEntity');
	}
}

$mcontroller->loadEntity();
$janus_meta = $mcontroller->getMetadata();

$idpmeta2 = array();

foreach($janus_meta AS $data) {
	if(preg_match('/entity:name:([\w]{2})$/', $data->getKey(), $matches)) {
		$idpmeta2['name'][$matches[1]] = $data->getValue();
	} elseif(preg_match('/entity:description:([\w]{2})$/', $data->getKey(), $matches)) {
		$idpmeta2['description'][$matches[1]] = $data->getValue();
	} elseif(preg_match('/entity:url:([\w]{2})$/', $data->getKey(), $matches)) {
		$idpmeta2['url'][$matches[1]] = $data->getValue();
	} else {
		$idpmeta2[$data->getKey()] = $data->getValue();
	}
}

try {
	$idpentityid = $entity->getEntityid();
	
	$certInfo = SimpleSAML_Utilities::loadPublicKey($idpmeta2, TRUE);
	$certFingerprint = $certInfo['certFingerprint'];
	if (count($certFingerprint) === 1) {
		/* Only one valid certificate. */
		$certFingerprint = $certFingerprint[0];
	}
	
	$metaArray = array(
		'description' => $idpmeta2['description'],
		'url' => $idpmeta2['url'],
		'SingleSignOnService' => $idpmeta2['SingleSignOnService'],
		'SingleLogoutService' => $idpmeta2['SingleLogoutService'],
		'certFingerprint' => $certFingerprint,
	);

	if(array_key_exists('name', $idpmeta2)) {
		$metaArray['name'] = $idpmeta2['name'];
	}

	if(array_key_exists('SingleLogoutServiceResponse', $idpmeta2)) {
		$metaArray['SingleLogoutServiceResponse'] = $idpmeta2['SingleLogoutServiceResponse'];
	}
	
	if(array_key_exists('NameIDFormat', $idpmeta2)) {
		$metaArray['NameIDFormat'] = $idpmeta2['NameIDFormat'];
	} else {
		$metaArray['NameIDFormat'] = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
	}

	$metaflat = '// Revision: '. $entity->getRevisionid() ."\n";	
	$metaflat .= var_export($idpentityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';


	$metaArray['certData'] = $certInfo['certData'];
	$metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($idpentityid);
	$metaBuilder->addMetadataIdP20($metaArray);
	
	$metaBuilder->addContact('technical', array(
		'emailAddress' => $idpmeta2['contact:email'],
		'name' => $idpmeta2['contact:name'],
		));
	$metaxml = $metaBuilder->getEntityDescriptorText();

	/* Sign the metadata if enabled. */
	//$metaxml = SimpleSAML_Metadata_Signer::sign($metaxml, $idpmeta, 'SAML 2 IdP');

	if (array_key_exists('output', $_GET) && $_GET['output'] == 'xhtml') {
		$defaultidp = $config->getValue('default-saml20-idp');
		
		$t = new SimpleSAML_XHTML_Template($config, 'metadata.php', 'admin');
	
		$t->data['header'] = 'saml20-idp';
		$t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
		$t->data['metadata'] = htmlentities($metaxml);
		$t->data['metadataflat'] = htmlentities($metaflat);
		$t->data['defaultidp'] = $defaultidp;
		$t->show();
			
	} else {
		header('Content-Type: application/xml');
		echo $metaxml;
		exit(0);
	}
} catch(Exception $exception) {
	SimpleSAML_Utilities::fatalError($session->getTrackID(), 'METADATA', $exception);
}
?>
