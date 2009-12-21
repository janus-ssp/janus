<?php
/**
 * @author Sixto Martín, <smartin@yaco.es>
 * @author Jacob Christiaansen, <jach@wayf.dk>
 */

// Init config
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

// Set state
// TODO Read this from config file
$export_state = 'testaccepted';

// Get type filter
$export_type = null;
if(isset($_GET['type'])) {
    if(is_array($_GET['type'])) {
        $export_type = $_GET['type'];
    } else {
        $export_type = array($_GET['type']);
    }
}

$exclude_entityid = null;
if(isset($_GET['exclude_entityid'])) {
    $exclude_entityid = $_GET['exclude_entityid']; 
}

// Create a AdminUtil object
$util = new sspmod_janus_AdminUtil();

// Generate metadata
try {
    $entities = $util->getEntitiesByStateType($export_state, $export_type);

    $xml = new DOMDocument();
    $entitiesDescriptor = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'EntitiesDescriptor');
    $entitiesDescriptorName = $janus_config->getString('export.entitiesDescriptorName', 'Federation');
    $entitiesDescriptor->setAttribute('Name', $entitiesDescriptorName);
    $xml->appendChild($entitiesDescriptor);

    foreach ($entities as $entity) {

        if($entity['entityid'] == $exclude_entityid) {
            continue;
        }

        $entityDescriptor = sspmod_janus_MetaExport::getXMLMetadata(
            $entity['eid'], 
            $entity['revisionid'], 
            array(
                'maxCache' => $janus_config->getValue('maxCache', NULL), 
                'maxDuration' => $janus_config->getValue('maxDuration', NULL)
            )
        );

        if(empty($entityDescriptor) || !$entityDescriptor) {
            continue;
        }

        $entitiesDescriptor->appendChild($xml->importNode($entityDescriptor, TRUE));
    }

    /* Sign the metadata if enabled. */

    if ($janus_config->getBoolean('sign.enable', FALSE)) {
        $signer = new SimpleSAML_XML_Signer(
            array(
                'privatekey' => $janus_config->getString('sign.privatekey'),
                'privatekey_pass' => $janus_config->getString('sign.privatekey_pass', NULL),
                'certificate' => $janus_config->getString('sign.certificate'),
                'id' => 'ID',
            )
        );
        $signer->sign($entitiesDescriptor, $entitiesDescriptor, $entitiesDescriptor->firstChild);
    }

    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="federation.xml"');
    echo($xml->saveXML());

} catch(Exception $exception) {
    $session = SimpleSAML_Session::getInstance();
    SimpleSAML_Utilities::fatalError($session->getTrackID(), 'METADATA', $exception);
}
?>
