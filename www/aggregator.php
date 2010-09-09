<?php
/**
 * Metadata aggregator for JANUS
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Site
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     Sixto Martín, <smartin@yaco.es>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.5.0
 */

// Init config
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

// Get list of allowed aggregats
$aggregators = $janus_config->getArray('aggregators', null);

$id = null;
if(isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $session = SimpleSAML_Session::getInstance();
    SimpleSAML_Utilities::fatalError($session->getTrackID(), 'AGGREGATORID', $exception);
}

$export_state = $aggregators[$id]['state'];
$export_type = $aggregators[$id]['type'];

$exclude_entityid = null;
if(isset($_GET['exclude_entityid'])) {
    $exclude_entityid = $_GET['exclude_entityid']; 
}

// Create a AdminUtil object
$util = new sspmod_janus_AdminUtil();

// Generate metadata
try {
    $maxCache => $janus_config->getValue('maxCache', NULL);
    $maxDuration => $janus_config->getValue('maxDuration', NULL);

    $entities = $util->getEntitiesByStateType($export_state, $export_type);

    $xml = new DOMDocument();
    $entitiesDescriptor = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'EntitiesDescriptor');
    $entitiesDescriptorName = $janus_config->getString('export.entitiesDescriptorName', 'Federation');
    $entitiesDescriptor->setAttribute('Name', $entitiesDescriptorName);
    
    if($maxCache !== NULL) {
        $entitiesDescriptor->setAttribute('cacheDuration', 'PT' . $maxCache . 'S');
    }
    if($maxDuration !== NULL) {
        $entitiesDescriptor->setAttribute('validUntil', SimpleSAML_Utilities::generateTimestamp(time() + $maxDuration));
    }

    $xml->appendChild($entitiesDescriptor);

    foreach ($entities as $entity) {

        if($entity['entityid'] == $exclude_entityid) {
            continue;
        }

        $entityDescriptor = sspmod_janus_MetaExport::getXMLMetadata(
            $entity['eid'], 
            $entity['revisionid'], 
            array(
                'maxCache' => $maxCache, 
                'maxDuration' => $maxDuration
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
    SimpleSAML_Utilities::fatalError($session->getTrackID(), 'AGGREGATOR', $exception);
}
?>
