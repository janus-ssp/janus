<?php

ini_set('display_errors', true);
require '_includes.php';

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
$rootCertificatesFile = $janusConfig->getString('ca_bundle_file');

$server = new EntityEndpointsServer();
$server->setTrustedRootCertificateAuthoritiesFile($rootCertificatesFile);
$server->serve($_GET['eid']);

class EntityEndpointsServer
{
    protected $_response;

    protected $_endpointMetadataFields = array(
        'SingleSignOnService',
        'AssertionConsumerService',
        'SingleLogoutService'
    );

    protected $_entityMetadata;

    protected $_trustedRootCertificateAuthoritiesFile;

    public function __construct()
    {
        $this->_initializeResponse();
    }

    public function setTrustedRootCertificateAuthoritiesFile($file)
    {
        $this->_trustedRootCertificateAuthoritiesFile = $file;
    }

    protected function _initializeResponse()
    {
        $response = new stdClass();
        $this->_response = $response;
    }

    protected function _loadEntityMetadata($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_janus_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        $this->_entityMetadata = $entityController->getMetaArray();
    }

    public function serve($entityId)
    {
        if (isset($this->_trustedRootCertificateAuthoritiesFile)) {
            sspmod_janus_OpenSsl_Certificate_Chain_Factory::loadRootCertificatesFromFile(
                $this->_trustedRootCertificateAuthoritiesFile
            );
        }
        
        $this->_loadEntityMetadata($entityId);

        foreach ($this->_endpointMetadataFields as $endPointMetaKey) {
            if (!isset($this->_entityMetadata[$endPointMetaKey])) {
                // This entity does not have this binding
                continue;
            }

            $responsesByHost = array();
            foreach ($this->_entityMetadata[$endPointMetaKey] as $index => $binding) {
                $endpointResponse = new stdClass();
                $endpointResponse->CertificateChain = array();
                $endpointResponse->Errors = array();
                $endpointResponse->Warnings = array();

                $key = $endPointMetaKey . $index;
                $this->_response->$key = $endpointResponse;
                $endpointResponse = &$this->_response->$key;

                if (!isset($binding['Location']) || trim($binding['Location'])==="") {
                    $endpointResponse->Errors[] = "Binding has no Location?";
                    continue;
                }
                else {
                    $endpointResponse->Url = $binding['Location'];
                }

                try {
                    $sslUrl = new sspmod_janus_OpenSsl_Url($binding['Location']);
                }
                catch (Exception $e) {
                    $endpointResponse->Errors[] = "Endpoint is not a valid URL";
                    continue;
                }

                if (!$sslUrl->isHttps()) {
                    $endpointResponse->Errors[] = "Endpoint is not HTTPS";
                    continue;
                }

                // If this endpoint is the same hostename as a previous one there is no point in doing
                // all that expensive work all over again.
                $sslUrlHostname = $sslUrl->getHostName();
                if (isset($responsesByHost[$sslUrlHostname])) {
                    $cachedEndpointResult = $responsesByHost[$sslUrlHostname];
                    $endpointResponse->CertificateChain = $cachedEndpointResult->CertificateChain;
                    $endpointResponse->Errors           = $cachedEndpointResult->Errors;
                    $endpointResponse->Warnings         = $cachedEndpointResult->Warnings;
                    continue;
                }
                else {
                    $responsesByHost[$sslUrlHostname] = $endpointResponse;
                }

                $connectSuccess = $sslUrl->connect();
                if (!$connectSuccess) {
                    $endpointResponse->Errors[] = "Endpoint is unreachable";
                    continue;
                }

                if (!$sslUrl->isCertificateValidForUrlHostname()) {
                    $urlHostName = $sslUrl->getHostName();
                    $validHostNames = $sslUrl->getServerCertificate()->getValidHostNames();
                    $endpointResponse->Errors[] = "Certificate does not match the hostname '$urlHostName' (instead it matches " . implode(', ', $validHostNames) . ")";
                }

                $urlChain = $sslUrl->getServerCertificateChain();

                $certificates = $urlChain->getCertificates();
                foreach ($certificates as $certificate) {
                    $certificateSubject = $certificate->getSubject();

                    $endpointResponse->CertificateChain[] = array(
                        'Subject' => array(
                            'DN' => $certificate->getSubjectDn(),
                            'CN' => (isset($certificateSubject['CN'])?$certificateSubject['CN']:$certificateSubject['O']),
                        ),
                        'SubjectAlternative' => array(
                            'DNS' => $certificate->getSubjectAltNames(),
                        ),
                        'Issuer' => array(
                            'Dn' => $certificate->getIssuerDn(),
                        ),
                        'NotBefore' => array(
                            'UnixTime' => $certificate->getValidFromUnixTime(),
                        ),
                        'NotAfter' => array(
                            'UnixTime' => $certificate->getValidUntilUnixTime(),
                        ),
                        'RootCa'     => $certificate->getTrustedRootCertificateAuthority(),
                        'SelfSigned' => $certificate->isSelfSigned(),
                    );
                }

                $urlChainValidator = new sspmod_janus_OpenSsl_Certificate_Chain_Validator($urlChain);
                $urlChainValidator->validate();

                $endpointResponse->Warnings = array_merge($endpointResponse->Warnings, $urlChainValidator->getWarnings());
                $endpointResponse->Errors   = array_merge($endpointResponse->Errors,   $urlChainValidator->getErrors());
            }
        }
        $this->_sendResponse();
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
        echo json_encode($this->_response);
    }
}