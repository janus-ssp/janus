<?php

use Janus\ServiceRegistry\Entity\ConnectionRepository;

require __DIR__ . '/_includes.php';

set_time_limit(180);

// Note: creating the security context also sets the token which is necessary
// for Doctrine to set the authenticated user on updating an entity
// Since this can be easily forgotten in the current 'page script' setup
// This seems to be one more reason to handle routing and access control
// by a central framework, see also: https://github.com/janus-ssp/janus/issues/477
sspmod_janus_DiContainer::getInstance()->getSecurityContext();

// Initial import
$session        = SimpleSAML_Session::getInstance();
$config         = SimpleSAML_Configuration::getInstance();
$janusConfig    = sspmod_janus_DiContainer::getInstance()->getConfig();
$csrfProvider   = sspmod_janus_DiContainer::getInstance()->getCsrfProvider();

// Get data from config
/** @var string $authenticationSource */
$authenticationSource = $janusConfig->getValue('auth', 'login-admin');
/** @var string $userIdAttribute */
$userIdAttribute = $janusConfig->getValue('useridattr', 'eduPersonPrincipalName');

// Block invalid users.
if (!$session->isValid($authenticationSource)) {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirectTrustedUrl(SimpleSAML_Module::getModuleURL('janus/index.php'));
    exit;
}

$attributes = $session->getAttributes();
// Check if user id exists
if (!isset($attributes[$userIdAttribute])) {
    throw new Exception('User ID is missing');
}
$userId = $attributes[$userIdAttribute][0];
$user = new sspmod_janus_User($janusConfig->getValue('store'));
$user->setUserid($userId);
$user->load(sspmod_janus_User::USERID_LOAD);

$importData = $session->getData('string', 'import');
$importType = $session->getData('string', 'import_type');

if (!$importData && !$importType) {
    throw new SimpleSAML_Error_Exception('Nothing to import!');
}

if (!isset($_GET['eid'])) {
    throw new SimpleSAML_Error_Exception('No entity selected!');
}
$eid = (string) (int) $_GET['eid'];

$excludedMetadataKeys = array();
if (isset($_POST['excluded_metadata_keys']) && is_array($_POST['excluded_metadata_keys'])) {
    $excludedMetadataKeys = $_POST['excluded_metadata_keys'];
}

$factory = new \Janus\ServiceRegistry\UnmarshallerFactory();
$unmarshaller = $factory->createForType($importType);

$newRevision = $unmarshaller->unmarshall($importData);

/** @var ConnectionRepository $repository */
$repository = sspmod_janus_DiContainer::getInstance()->getEntityManager()->getRepository('');
$latestConnectionRevisions = $repository->findLatestRevisionsWithFilters(array('id' => $eid));
if (empty($latestConnectionRevisions)) {
    throw new \RuntimeException('Unable to find connection for id: ' . $eid);
}
if (count($latestConnectionRevisions) > 1) {
    throw new \RuntimeException('Multiple connections found for id: ' . $eid);
}
$currentLatestConnectionRevision = current($latestConnectionRevisions);

$comparator = new \Janus\ServiceRegistry\RevisionComparator();
$comparator->compare($latestConnectionRevision, $newRevision);

$importer = new \Janus\ServiceRegistry\Importer();
$results = $importer->import($importData, $connection, $excludedMetadataKeys);

if (!empty($_POST) && isset($_POST['apply'])) {
    if (!isset($_POST['csrf_token']) || !$csrfProvider->isCsrfTokenValid('import_entity', $_POST['csrf_token'])) {
        SimpleSAML_Logger::warning('Janus: [SECURITY] CSRF token not found or invalid');
        throw new SimpleSAML_Error_BadRequest('Missing valid csrf token!');
    }

    $newRevision = $results->persist();

    if ($newRevision) {
        // Notify users who have asked to be updated when
        $pm = new sspmod_janus_Postman();
        $addresses[] = 'ENTITYUPDATE-' . $newRevision->getConnection()->getId();
        $editLink = SimpleSAML_Module::getModuleURL(
            'janus/editentity.php',
            array(
                'eid' => $newRevision->getConnection()->getId(),
                'revisionid' => $newRevision->getId()
            )
        );
        $pm->post(
            'Entity updated - ' . $newRevision->getConnection()->getName(),
            'Permalink: <a href="' . htmlspecialchars($editLink) . '">'
            . htmlspecialchars($editLink)
            . '</a><br /><br />'
            . htmlspecialchars($newRevision->getRevisionNote())
            . '<br /><br />'
            . htmlspecialchars($note),
            $addresses,
            $user->getUid()
        );
    }

    $session->deleteData('string', 'import');
    $session->deleteData('string', 'import_type');

    SimpleSAML_Utilities::redirectTrustedUrl(
        SimpleSAML_Module::getModuleURL('janus/editentity.php'),
        array(
            'eid' => $entity->getEid(),
            'revisionid' => $entity->getRevisionid(),
        )
    );
    exit;
}

$et = new SimpleSAML_XHTML_Template($config, 'janus:importentity.php', 'janus:editentity');
$et->data['header'] = 'JANUS';
$et->data['results'] = $results;
$et->show();
