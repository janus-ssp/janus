<?php

ini_set('display_errors', true);
require '_includes.php';

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');

$server = new EntityValidationServer();
$server->serve($_GET['eid']);

class EntityValidationServer
{
    protected $_response;

    /**
     * @var sspmod_janus_EntityController
     */
    protected $_entityController;

    public function __construct()
    {
        $this->_initializeResponse();
    }

    protected function _initializeResponse()
    {
        $response = new stdClass();
        $response->Validations = array();
        $response->Errors = array();
        $this->_response = $response;
    }

    public function serve($entityId)
    {
        if (!$this->_loadEntity($entityId)) {
            SimpleSAML_Logger::debug('No entity found!');
            return $this->_sendResponse();
        }

        $this->_checkMetadataValidity();
        return $this->_sendResponse();
    }

    protected function _loadEntity($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_janus_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        $this->_entityController = $entityController;

        return $entityController ? true : false;
    }

    protected function _checkMetadataValidity()
    {
        $validator = new sspmod_janus_Metadata_Validator($this->_entityController);
        $validator->validate();

        $this->_response->Errors = $validator->getErrors();
        $this->_response->Validations = $validator->getValidations();
    }

    protected function _sendResponse()
    {
        $this->_outputContentType('application/json');
        $this->_outputResponse();
    }

    protected function _outputContentType($contentType)
    {
        header("Content-Type: $contentType");
    }

    protected function _outputResponse()
    {
        //echo '<pre>'; var_dump($this->_response->Validations, true);
        echo json_encode($this->_response);
    }
}