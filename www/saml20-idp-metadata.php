<?php
/*
 * Generate IdP metadata
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @author Sixto Martín, <smartin@yaco.es>
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
$requiredmeta = $janus_config->getArray('metadatafields.saml20-idp');

foreach($requiredmeta AS $k => $v) {
    if(array_key_exists('required', $v) && $v['required'] === true) {
        $required[] = $k;
    }
}

$metadata = array();
foreach($janus_meta AS $k => $v) {
    $metadata[] = $v->getKey();
}

$missing_required = array_diff($required, $metadata);

if (empty($missing_required)) {
    try {
        $idpentityid = $entity->getEntityid();

        $blocked_entities = $mcontroller->getBlockedEntities();
        $disable_consent = $mcontroller->getDisableConsent();

        $metaArray = $mcontroller->getMetaArray();

        $metaflat = '// Revision: '. $entity->getRevisionid() ."\n";
        $metaflat .= var_export($idpentityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

        if(!empty($blocked_entities)) {
            $metaflat = substr($metaflat, 0, -2);
            $metaflat .= "  'authproc' => array(\n";
            $metaflat .= "    10 => array(\n";
            $metaflat .= "      'class' => 'janus:AccessBlocker',\n";
            $metaflat .= "      'blocked' => array(\n";

            foreach($blocked_entities AS $blocked_entity => $value) {
                $metaflat .= "        '". $blocked_entity ."',\n";
            }

            $metaflat .= "      ),\n";
            $metaflat .= "    ),\n";
            $metaflat .= "  ),\n";
            $metaflat .= '),';
        }
        if(!empty($disable_consent)) {
            $metaflat = substr($metaflat, 0, -2);
            $metaflat .= "  'consent.disable' => array(\n";

            foreach($disable_consent AS $key => $value) {
                $metaflat .= "    '". $key ."',\n";
            }

            $metaflat .= "  ),\n";
            $metaflat .= '),';
        }

        $metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($idpentityid);
        $metaBuilder->addMetadataIdP20($metaArray);

        if(!empty($metaArray['contact'])) {
            $metaBuilder->addContact('technical', $metaArray['contact']);
        }
        if(!empty($metaArray['organization'])) {
            $metaBuilder->addOrganizationInfo($metaArray['organization']);
        }

        $metaxml = $metaBuilder->getEntityDescriptorText();

        if (array_key_exists('output', $_GET) && $_GET['output'] == 'xhtml') {
            $t = new SimpleSAML_XHTML_Template($config, 'janus:metadata.php', 'janus:janus');

            $t->data['header'] = 'Metadata export - IdP';
            $t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
            $t->data['metadata'] = htmlentities($metaxml);
            $t->data['metadataflat'] = htmlentities($metaflat);
            $t->data['revision'] = $entity->getRevisionid();
            $t->data['entityid'] = $idpentityid;
            $t->data['eid'] = $entity->getEid();

            if(isset($_GET['send_mail'])) {
                $t->data['send_mail'] = TRUE;
                $t->data['mail'] = $userid;
            }
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
    $t = new SimpleSAML_XHTML_Template($config, 'janus:error.php', 'janus:error');
    $t->data['header'] = 'error_required_metadata_missing_header';
    $t->data['error'] = 'error_required_metadata_missing';
    $t->data['extra_data'] = implode("\n", $missing_required);
    $t->show();
}
?>
