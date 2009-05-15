<?php

/* Load simpleSAMLphp, configuration and metadata */
$config = SimpleSAML_Configuration::getInstance();
$session = SimpleSAML_Session::getInstance();
// JANUS stuff
$janus_config = $config->copyFromBase('janus', 'module_janus.php');
if (!$session->isValid('janus') ) {
	SimpleSAML_Utilities::redirect(
		SimpleSAML_Module::getModuleURL('janus/janus-login.php'),
	   	array('RelayState' => SimpleSAML_Utilities::selfURL())
	);
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
		$spmeta['name'][$matches[1]] = $data->getValue();
	} elseif(preg_match('/entity:description:([\w]{2})$/', $data->getKey(), $matches)) {
		$spmeta['description'][$matches[1]] = $data->getValue();
	} elseif(preg_match('/entity:url:([\w]{2})$/', $data->getKey(), $matches)) {
		$spmeta['url'][$matches[1]] = $data->getValue();
	} else {
		$spmeta[$data->getKey()] = $data->getValue();
	}
}

try {
	$spentityid = $entity->getEntityid();
	
	$metaArray = array();

	if(array_key_exists('SingleLogoutService', $spmeta)) {
		$metaArray['SingleLogoutService'] = $spmeta['SingleLogoutService'];
	}
	if(array_key_exists('AssertionConsumerService', $spmeta)) {
		$metaArray['AssertionConsumerService'] = $spmeta['AssertionConsumerService'];
	}
	if (array_key_exists('NameIDFormat', $spmeta)) {
		$metaArray['NameIDFormat'] = $spmeta['NameIDFormat'];
	} else {
		$metaArray['NameIDFormat'] = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
	}
	if (array_key_exists('name', $spmeta)) {
		$metaArray['name'] = $spmeta['name'];
	}
	if (array_key_exists('description', $spmeta)) {
		$metaArray['description'] = $spmeta['description'];
	}
	if (array_key_exists('url', $spmeta)) {
		$metaArray['url'] = $spmeta['url'];
	}

	$certInfo = SimpleSAML_Utilities::loadPublicKey($spmeta);
	if ($certInfo !== NULL && array_key_exists('certData', $certInfo)) {
		$metaArray['certData'] = $certInfo['certData'];
	}

	$metaflat = '// Revision: '. $entity->getRevisionid() ."\n";
	$metaflat .= var_export($spentityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

	$metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($spentityid);
	$metaBuilder->addMetadataSP20($metaArray);
	$metaBuilder->addContact('technical', array(
		'emailAddress' => $spmeta['contact:email'],
		'name' => $spmeta['contact:name'],
	));
	$metaxml = $metaBuilder->getEntityDescriptorText();

	/* Sign the metadata if enabled. */
	//$metaxml = SimpleSAML_Metadata_Signer::sign($metaxml, $spmeta, 'SAML 2 SP');
	
	if (array_key_exists('output', $_REQUEST) && $_REQUEST['output'] == 'xhtml') {
		
		$t = new SimpleSAML_XHTML_Template($config, 'metadata.php', 'admin');
	
		$t->data['header'] = 'saml20-sp';
		$t->data['metadata'] = htmlentities($metaxml);
		$t->data['metadataflat'] = htmlentities($metaflat);
		$t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
		
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
