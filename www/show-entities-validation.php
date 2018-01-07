<?php

require __DIR__ . '/_includes.php';

$janusConfig = sspmod_janus_DiContainer::getInstance()->getConfig();
$authSource = $janusConfig->getValue('auth', 'login-admin');

$as = new \SimpleSAML\Auth\Simple($authSource);

// Validate user
if (!$as->isAuthenticated()) {
    SimpleSAML_Utilities::redirectTrustedUrl(
        \SimpleSAML\Module::getModuleURL('janus/index.php', array('selectedtab'=>"'federation'"))
    );
}

$entities = array();

$util = new sspmod_janus_AdminUtil();
$userController = sspmod_janus_DiContainer::getInstance()->getUserController();
$entities = array_merge($userController->searchEntitiesByType('saml20-idp'), $userController->searchEntitiesByType('saml20-sp'));

foreach ($entities as $entity) {
    /**
     * @var sspmod_janus_Entity $entity
     */
    $entityId = $entity->getEid();

    $entityController = sspmod_janus_DiContainer::getInstance()->getEntityController();
    $entityController->setEntity($entityId);
    $entityController->loadEntity();

    $controllerEntity = $entityController->getEntity();

    $entityType         = $controllerEntity->getType();
    if (!isset($entities[$entityType])) {
        $entities[$entityType] = array();
    }
    $entities_info[$entityType][] = array(
        'Id'                => $controllerEntity->getEntityid(),
        'Name'              => $controllerEntity->getPrettyname(),
        'WorkflowStatus'    => $controllerEntity->getWorkflow(),
        'MetadataUrl'       => $controllerEntity->getMetadataURL(),
        'Eid'               => $controllerEntity->getEid(),
    );
}
ksort($entities_info);
$template = new SimpleSAML_XHTML_Template(
    SimpleSAML_Configuration::getInstance(),
    'janus:show-entities-validation.php',
    'janus:show-entities-validation'
);

$template->data['header'] = "Service Registry JANUS entities validation";
$template->data['entities'] = $entities_info;
$template->show();
