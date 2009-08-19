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


$mcontroller = new sspmod_janus_EntityController($janus_config);

$eid = $_GET['eid'];
$revisionid = -1;

if(isset($_GET['revisionid'])) {
	$revisionid = $_GET['revisionid'];
}

if($revisionid > -1) {
	if(!$entity = $mcontroller->setEntity($eid, $revisionid)) {
		die('Error in setEntity');
	}
} else {
	if(!$entity = &$mcontroller->setEntity($eid)) {
		die('Error in setEntity');
	}
}

$mcontroller->loadEntity();
$janus_meta = $mcontroller->getMetadata();
$requiredmeta = $janus_config->getArray('required.metadatafields.saml20-sp');

$metadata = array();
foreach($janus_meta AS $k => $v) {
    $metadata[] = $v->getKey();
}

$missing_required = array_diff($requiredmeta, $metadata);

if (empty($missing_required)) {
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

        $blocked_entities = $mcontroller->getBlockedEntities();

        $metaflat = '// Revision: '. $entity->getRevisionid() ."\n";
        $metaflat .= var_export($spentityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

        if(!empty($blocked_entities)) {
            $metaflat = substr($metaflat, 0, -2);
            $metaflat .= "  'authproc' => array(\n";
            $metaflat .= "    10 => array(\n";
            $metaflat .= "      'class' => 'janus:AccessBlocker',\n";
            $metaflat .= "      'blocked' => array(\n";

            foreach($blocked_entities AS $entity => $value) {
                $metaflat .= "        '". $entity ."',\n";	
            }



            $metaflat .= "      ),\n";
            $metaflat .= "    ),\n";
            $metaflat .= "  ),\n";


            $metaflat .= '),';
        }

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
            $t = new SimpleSAML_XHTML_Template($config, 'janus:metadata.php', 'janus:janus');

            $t->data['header'] = 'Metadata export';
            $t->data['metadata_intro'] = 'Her er lidt tekst';
            $t->data['metadata'] = htmlentities($metaxml);
            $t->data['metadataflat'] = htmlentities($metaflat);
            $t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
            $t->data['revision'] = $entity->getRevisionid();
            $t->data['entityid'] = $spentityid;
            $t->data['eid'] = $entity->getEid();

            $t->show();
        } else {
            header('Content-Type: application/xml');	
            echo $metaxml;
            exit(0);
        }
    } catch(Exception $exception) { 
        SimpleSAML_Utilities::fatalError($session->getTrackID(), 'METADATA', $exception);
    }
} else {
    $t = new SimpleSAML_XHTML_Template($config, 'janus:error.php', 'janus:janus');
    $t->data['header'] = 'Required metadatafields are missing';
    $t->data['error'] = 'The following metadatafields are required but not present.';
    $t->data['extra_data'] = implode("\n", $missing_required);
    $t->show();
}
?>
