<?php
/*
 * Generate metadata
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package SimpleSAMLphp
 * @subpackeage JANUS
 */

require __DIR__ . '/_includes.php';

/* Load simpleSAMLphp, configuration and metadata */
$session = SimpleSAML_Session::getSessionFromRequest();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = sspmod_janus_DiContainer::getInstance()->getConfig();

$authSource = $janus_config->getValue('auth', 'login-admin');
$userIdAttr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

if ($session->isValid($authSource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$userIdAttr])) {
        throw new Exception('User ID is missing');
    }
    $userId = $attributes[$userIdAttr][0];
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirectTrustedUrl(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

if (isset($_GET['eid'])) {
    $eid = $_GET['eid'];
} else {
    throw new SimpleSAML_Error_Exception('Eid must be set');
}

if (isset($_GET['revisionid'])) {
    $revisionId = $_GET['revisionid'];
} else {
    throw new SimpleSAML_Error_Exception('URL parameter "revisionid" must be provided.');
}

$md_options = $janus_config->getValue('mdexport.default_options');

$metaXml = sspmod_janus_MetaExport::getReadableXMLMetadata(
    $eid,
    $revisionId,
    array(
        'maxCache' => $md_options['maxCache'],
        'maxDuration' => $md_options['maxDuration'],
    )
);

$metaFlat = sspmod_janus_MetaExport::getFlatMetadata($eid, $revisionId);

$metaArray = sspmod_janus_MetaExport::getPHPArrayMetadata($eid, $revisionId);

// Error generating some of the metadata
if (empty($metaFlat) || empty($metaXml)) {
    $t = new SimpleSAML_XHTML_Template($config, 'janus:error.php', 'janus:error');
    $t->data['header'] = 'JANUS';
    $t->data['title'] = 'error_required_metadata_missing_header';
    $t->data['error'] = 'error_required_metadata_missing';
    $t->data['extra_data'] = '<ul><li>' .implode("</li>\n<li>", sspmod_janus_MetaExport::getError()) . '</li></ul>';
    $t->show();
    exit(0);
}

if (array_key_exists('output', $_GET) && $_GET['output'] === 'xhtml') {
    $t = new SimpleSAML_XHTML_Template($config, 'janus:metadata.php', 'janus:metadata');

    $t->data['header'] = 'Metadata export';
    $t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
    $t->data['metadata'] = htmlentities($metaXml);
    $t->data['metadataflat'] = htmlentities($metaFlat, ENT_COMPAT, 'UTF-8');
    $t->data['metadatajson'] = json_encode($metaArray);
    $t->data['revision'] = $revisionId;
    $t->data['eid'] = $eid;
    $t->show();
    exit(0);
}

if (array_key_exists('output', $_GET) && $_GET['output'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode($metaArray);
    exit(0);
}

header('Content-Type: application/xml');
echo $metaXml;
exit(0);
