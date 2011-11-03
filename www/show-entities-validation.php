<?php

$session = SimpleSAML_Session::getInstance();

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
$authSource = $janusConfig->getValue('auth', 'login-admin');

// Validate user
if (!$session->isValid($authSource)) {
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php', array('selectedtab'=>"'federation'")));
}

$entities = array();

$util = new sspmod_janus_AdminUtil();
$userController = new sspmod_janus_UserController($janusConfig);
$entities = array_merge($userController->searchEntitiesByType('saml20-idp'), $userController->searchEntitiesByType('saml20-sp'));

foreach ($entities as $entity) {
    /**
     * @var sspmod_janus_Entity $entity
     */
    $entityId = $entity->getEid();

    $entityController = new sspmod_janus_EntityController($janusConfig);
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
