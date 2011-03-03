<?php
/**
 * Federation status page
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
 * @category SimpleSAMLphp
 * @package  JANUS
 * @author   Sixto Martín, <smartin@yaco.es>
 * @author   Jacob Christiansen <jach@wayf.dk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  SVN: $Id$
 * @link     http://code.google.com/p/janus-ssp/
 */
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$metaentries = array(
    'saml20-idp' => array(),
    'saml20-sp' => array(),
    'shib13-idp' => array(),
    'shib13-sp' => array(),
);

$now = time();
$util = new sspmod_janus_AdminUtil();

if (SimpleSAML_Module::isModuleEnabled('x509')) {
    $strict_cert_validation = $janus_config->getBoolean('cert.strict.validation', true);
    $cert_allowed_warnings = $janus_config->getArray('cert.allowed.warnings', array());
    $cert_time_limit = $janus_config->getInteger('notify.cert.expiring.before', 30);
}

$notify_meta_expiring_before = $janus_config->getInteger('notify.meta.expiring.before', 5);
$meta_time_limit = $now + ($notify_meta_expiring_before * 86400);

$workflowstates = $janus_config->getValue('workflowstates');

foreach ($util->getEntities() as $entity) {
    $entry = array();

    $eid = $entity['eid'];

    // Get Entity controller
    $mcontroller = new sspmod_janus_EntityController($janus_config);
    $mcontroller->setEntity($eid);
    $mcontroller->loadEntity();

    // Grab some basic fields
    $metadata = $mcontroller->getMetadata();
    $entity_id = $mcontroller->getEntity()->getEntityid();
    $entity_type = $mcontroller->getEntity()->getType();
    $prettyname = $mcontroller->getEntity()->getPrettyname();
    $entity_workflow = $mcontroller->getEntity()->getWorkflow();

    $metaArray = $mcontroller->getMetaArray();
    $entry['entityid'] = $entity_id;
    $entry['entitytype'] = $entity_type;
    $entry['prettyname'] = $prettyname;
    $entry['workflow'] = $entity_workflow;

    // Check if the entity has all the required fields
    $metadata_alowed = $janus_config->getArray('metadatafields.' . $entity_type, array());
    $metadata_required = array();

    foreach ($metadata_alowed AS $k => $v) {
        if (array_key_exists('required', $v) && $v['required'] === true) {
            $metadata_required[] = $k;
        }
    }

    $metadata_keys = array();
    foreach ($metadata AS $k => $v) {
        if ($v->getValue() !== '' && $v->getValue() !==  "defaultvalue" && $v->getValue() !== "CHANGE THIS") {
            $metadata_keys[] = $v->getKey();
        }
    }

    $missing_required = array_diff($metadata_required, $metadata_keys);

    $entry['invalid_metadata'] = false;
    if ($missing_required) {
        $entry['invalid_metadata'] = $missing_required;
    }

    // Now validate the certificate
    $entry['invalid_certificate'] = false;
    $entry['cert_status'] = 'no_data';
    if (!isset($metaArray['certData'])) {
        $entry['invalid_certificate'] = 'cert_not_found';
        $entry['cert_validation'] = 'bad';
    } else if (SimpleSAML_Module::isModuleEnabled('x509')) {
        $pem = trim($metaArray['certData']);
        $pem = chunk_split($pem, 64, "\r\n");
        $pem = substr($pem, 0, -1); // remove the last \n character
        $result = sspmod_x509_CertValidator::validateCert($pem, true, false);
        if ($result != 'cert_validation_success') {
            $entry['invalid_certificate'] = $result;
            $entry['cert_validation'] = ((!$strict_cert_validation && in_array($result, $cert_allowed_warnings)) ? 'poor' : 'bad');

            // Try one more time with OCSP
            $result2 = sspmod_x509_Certvalidator::validateCert($pem, false, true);
            if ($result2 == 'cert_validation_success') {
                $entry['cert_validation'] = 'good';
                $entry['invalid_certificate'] = false;
            }

        } else {
            $entry['cert_validation'] = 'good';
        }

        // Check if this cert entry is rotten
        $entry['cert_expiration_date'] = sspmod_x509_CertValidator::getDaysUntilExpiration($pem);
        if ($entry['cert_expiration_date'] < 0) {
            $entry['cert_status'] = 'no_data';
        } else if ($entry['cert_expiration_date'] == 0) {
            $entry['cert_status'] = 'expired';
        } else if ($entry['cert_expiration_date'] < $cert_time_limit) {
            $entry['cert_status'] = 'expires soon';
        } else {
            $entry['cert_status'] = 'expires';
        }
    } else {
        $entry['invalid_certificate'] = 'x509_module_not_enabled';
        $entry['cert_status'] = 'unknown'; 
    }

    // Check if this meta entry is rotten
    if (array_key_exists('expire', $metaArray)) {
        if ($metaArray['expire'] < $now) {
            $entry['meta_status'] = 'expired';
            $entry['meta_expiration_time'] = ($now - $metaArray['expire'])/3600;
        } else {
            if ($metaArray['expire'] < $meta_time_limit ) {
                $entry['meta_status'] = 'expires soon';
            } else {
                $entry['meta_status'] = 'expires';
            }
            $entry['meta_expiration_time'] = ($metaArray['expire'] - $now)/3600;
        }
    } else {
        $entry['meta_status'] = 'no_data';
    }

    // Fill in some more data
    $entry['name'] = (array_key_exists('name', $metaArray)) ? $metaArray['name'] : null;
    $entry['url'] = (array_key_exists('url', $metaArray)) ? $metaArray['url'] : null;

    // Check if we have a flag icon
    $entry['flag'] = null;
    $entry['flag_name'] = null;
    if (   SimpleSAML_Module::isModuleEnabled('metalisting')
        && (array_key_exists('tags', $metaArray))
    ) {
        $countries = array(
            'denmark' => 'dk',
            'finland' => 'fi',
            'france' => 'fr',
            'germany' => 'de',
            'norway' => 'no',
            'poland' => 'pl',
            'spain' => 'es',
            'sweden' => 'se',
            'switzerland' => 'ch',
        );
        foreach ($countries as $country_name => $code) {
            if (in_array($country_name, $metaArray['tags'])) {
                $entry['flag'] = SimpleSAML_Module::getModuleURL('metalisting/flags/' . $code . '.png');
                $entry['flag_name'] = $country_name;
                break;
            }
        }
    }

    // Store the data in the result array
    if (array_key_exists($entity_type, $metaentries)) {
        array_push($metaentries[$entity_type], $entry);
    }
}

if (!isset($_GET['output']) || $_GET['output'] !== 'json') {
    $config = SimpleSAML_Configuration::getInstance();
    $t = new SimpleSAML_XHTML_Template($config, 'janus:metalisting.php', 'janus:editentity');
    $t->data['header'] = $t->t('federation_entities_header');
    $t->data['metaentries'] = $metaentries;
    $t->data['workflowstates'] = $workflowstates;
    $t->show();
} else {
    $json = array();
    $type = null;
    if (isset($_GET['type'])) {	
        $type = $_GET['type'];
    }
    header('Content-type: application/json');
    header("Content-Disposition: attachment; filename=federation_metadata.json");

    foreach ($metaentries as $entity_type => $entry_set) {
        if (!isset($type) || (isset($type) && $type == $entity_type)) {
            foreach ($entry_set as $entry) {		
                $valid_status = 'bad';
                if ($entry['invalid_metadata'] == false && ($entry['meta_status'] == 'expires' || $entry['meta_status'] == 'expires soon' || $entry['meta_status'] == 'no_data')) {
                    if ($entry['cert_status'] == 'expires' || $entry['cert_status'] == 'expires soon') {
                        $valid_status = $entry['cert_validation']; 
                        if ($valid_status == 'no_data') {
                            $valid_status = 'unknown';
                        }
                    }
                }

                $json[] = array( 
                    'name'       => $entry['name'], 
                    'url'        => $entry['url'], 
                    'status'     => $valid_status, 
                    'entityid'   => $entry['entityid'], 
                    'entitytype' => $entry['entitytype'], 
                ); 
            } 
        } 
    } 
    // PHP versions prior to 5.2 don't have json_encode
    include_once dirname(__FILE__) . '/../lib/jsonwrapper/jsonwrapper.php';
    echo json_encode($json); 
}
