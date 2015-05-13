<?php

namespace Janus\ServiceRegistry\Importer;

use Psr\Log\LoggerInterface;
use SAML2_Const;
use SAML2_XML_md_EntityDescriptor;
use SAML2_XML_md_SPSSODescriptor;
use sspmod_janus_MetadataField;

class AssertionConsumerServiceMetadataFieldMapper implements MetadataFieldMapperInterface
{
    private $supportedSamlBindings = array(
        SAML2_Const::BINDING_HTTP_REDIRECT,
        SAML2_Const::BINDING_HTTP_POST,
        SAML2_Const::BINDING_SOAP,
        SAML2_Const::BINDING_HTTP_ARTIFACT,
        // @todo Add these to SAML2_Const:
        'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
        'urn:oasis:names:tc:SAML:2.0:bindings:URI',
    );

    /**
     * @var sspmod_janus_MetadataField[]
     */
    private $fields;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function import(SAML2_XML_md_EntityDescriptor $entityDescriptor) {
        $services = array();
        foreach ($entityDescriptor->RoleDescriptor as $role) {
            if (!$role instanceof SAML2_XML_md_SPSSODescriptor) {
                continue;
            }

            foreach ($role->AssertionConsumerService as $acsService) {
                if (!in_array($acsService->Binding, $this->supportedSamlBindings)) {
                    $this->logger->notice("Import skipping unsupported binding: " . $acsService->Binding);
                    continue;
                }

                $services[] = array($acsService->Binding, $acsService->Location);
            }
        }

        $fields = array();
        $index = 0;
        foreach ($services as $service) {
            $bindingFieldName = "AssertionConsumerService:{$index}:Binding";
            $bindingValue = $service[0];
            $locationFieldName = "AssertionConsumerService:{$index}:Location";
            $locationValue = $service[1];

            if (!isset($this->fields[$bindingFieldName]) || !isset($this->fields[$locationFieldName])) {
                $this->logger->notice("Import skipping AssertionConsumerService: field not configured: " . $bindingFieldName);
                continue;
            }

            $possibleValues = $this->fields[$bindingFieldName]->getSelectValues();
            if (!in_array($bindingValue, $possibleValues)) {
                $this->logger->notice(
                    sprintf(
                        "Import skipping AssertionConsumerService: binding '%s' not allowed by field configuration for '%s'.",
                        $bindingFieldName,
                        $bindingValue
                    )
                );
                continue;
            }

            $fields[$bindingFieldName] = $bindingValue;
            $fields[$locationFieldName] = $locationValue;
            $index++;
        }
        return $fields;
    }
}
