<?php

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

class sspmod_janus_Importer
{
    /**
     * @var string
     */
    private $_entityId;

    /**
     * @var sspmod_janus_EntityController
     */
    private $_entityController;

    /**
     * JANUS configuration
     * @var ConfigProxy
     */
    private $_config;

    /**
     * @var bool
     */
    private $_updated = false;

    /**
     * @var array
     */
    private $_excludedMetadataKeys;

    /**
     * @var string
     */
    private $_previousMemoryLimit;

    /**
     * @param sspmod_janus_EntityController $entityController
     * @param ConfigProxy $config
     */
    public function __construct(
        $entityId,
        sspmod_janus_EntityController $entityController,
        ConfigProxy $config,
        array $excludedMetadataKeys = array()
    ) {
        $this->_entityId = $entityId;
        $this->_entityController = $entityController;
        $this->_config = $config;
        $this->_excludedMetadataKeys = $excludedMetadataKeys;
    }

    public function hasPerformedUpdates()
    {
        return $this->_updated;
    }

    public function importSp($metadata)
    {
        return $this->import(
            $metadata,
            function(SimpleSAML_Metadata_SAMLParser $parser) {
                return $parser->getMetadata20SP();
            }
        );
    }

    public function importIdp($metadata)
    {
        return $this->import(
            $metadata,
            function(SimpleSAML_Metadata_SAMLParser $parser) {
                return $parser->getMetadata20IdP();
            }
        );
    }

    private function import($metadata, $parserFunction)
    {
        $this->startImport();

        // Parse metadata
        try {
            $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($metadata);
        } catch (Exception $e) {
            \SimpleSAML\Logger::error(
                'Importer - Metadata not valid SAML 2.0' .
                var_export($e, true)
            );

            $this->resetMemoryLimit();
            return 'error_not_valid_saml20';
        }

        \SimpleSAML\Logger::debug('Entities Found: '. count($entities));
        if (count($entities) > 1) {

            // We found multiple entities, So we have to loop through them
            // in order to select the entity ID which we want to import
            foreach($entities as $entityId => $parser) {

                if ($entityId === $this->_entityId) {
                    \SimpleSAML\Logger::debug('Matching EntityIDs found for: '. $entityId);

                    // Import metadata
                    \SimpleSAML\Logger::debug('Processing EntityID: '. $entityId);
                    $result = $this->importParsedMetadata($parserFunction($parser));
                    $this->resetMemoryLimit();
                    return $result;
                }
            }
            // Apparently the entity was not found in supplied metadata, Log error
            \SimpleSAML\Logger::error(
                'importMetadata20SP - EntityId not found'
            );

            $this->resetMemoryLimit();
            return 'error_entityid_not_found';

        } else if (count($entities) == 1) {
            $parser = $entities[key($entities)];
            $result = $this->importParsedMetadata($parserFunction($parser));
            $this->resetMemoryLimit();
            return $result;
        }

        // The parsed metadata contains no entities
        \SimpleSAML\Logger::error(
            'importMetadata20SP - EntityId not found'
        );

        return 'error_entityid_not_found';
    }

    private function importParsedMetadata($parsedMetadata)
    {
        // If metadata was not parsed
        if ($parsedMetadata === null) {
            \SimpleSAML\Logger::error(
                'Importer - Metadata was not parsed'
            );
            return 'error_metadata_not_parsed';
        }

        if (isset($parsedMetadata['expire']) && $parsedMetadata['expire'] < time()) {
            \SimpleSAML\Logger::error(
                'Importer - Metadata was not parsed due expiration'
            );
            return 'error_metadata_not_parsed_due_expiration';
        }

        // Remove entity descriptor
        unset($parsedMetadata['entityDescriptor']);
        unset($parsedMetadata['metadata-set']);

        // Validate that entity id is the same for imported metadata and entity
        if ($parsedMetadata['entityid'] != $this->_entityId) {
            \SimpleSAML\Logger::error(
                'Importer - EntityId does not match'
            );
            return 'error_entityid_no_match';
        } else {
            unset($parsedMetadata['entityid']);
        }

        $parsedMetadata = $this->_removeUnusedContacts($parsedMetadata);
        $parsedMetadata = $this->_removeNonSaml2Services($parsedMetadata);
        $parsedMetadata = $this->_applyRequestedAttributesAsArp($parsedMetadata);

        $converter = sspmod_janus_DiContainer::getInstance()->getMetaDataConverter();
        $parsedMetadata = $converter->execute($parsedMetadata);

        $msg = $this->_addCertificateMetaData($parsedMetadata);

        if ($msg) {
            return $msg;
        }

        foreach ($parsedMetadata AS $key => $value) {
            if (!empty($this->_excludedMetadataKeys) && in_array($key, $this->_excludedMetadataKeys)) {
                continue;
            }
            if ($this->_entityController->hasMetadata($key)) {
                if (!$this->_entityController->updateMetadata($key, $value)) {
                    \SimpleSAML\Logger::info(
                        'Importer - Metadata field ' . $key . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $this->_updated = true;
                }
            } else {
                if (!$this->_entityController->addMetadata($key, $value)) {
                    \SimpleSAML\Logger::info(
                        'Importer - Metadata field ' . $key . ' with value ' . $value . ' was not added.'
                    );
                } else {
                    $this->_updated = true;
                }
            }
        }

        return 'status_metadata_parsed_ok';
    }

    private function _addCertificateMetaData(&$parsedMetaData)
    {
        $encryptionEnabled = $this->_config->getBoolean('encryption.enable');
        $certKeys = array('keys:0:', 'keys:1:', 'keys:2:','keys:3:', 'keys:4:', 'keys:5:','keys:6:', 'keys:7:', 'keys:8:');
        $certDataKeys = array('certData','certData2','certData3');
        $certificates = array();

        foreach ($certKeys as $certKey) {
            if (!isset($parsedMetaData[$certKey . 'X509Certificate'])) {
                continue;
            }

            // Skip encryption keys (key with no signing use) if we don't support encryption usage.
            if (!$parsedMetaData[$certKey . 'signing'] && !$encryptionEnabled) {
                continue;
            }

            $certData = $parsedMetaData[$certKey . 'X509Certificate'];
            /*
             * We don't want an empty certData if keys:0 is an encryption key and encryption is not enabled. So we
             * ensure that we fill the $certDataKeys in the right order.
             */
            foreach ($certDataKeys as $certDataKey) {
                if (!isset($certificates[$certDataKey])) {
                    $certificates[$certDataKey] = str_replace(array(" ", "\r\n", "\n", "\r", "\t", "\x09"), '', $certData);
                    if (!$this->_validatePublicCertificate($certificates[$certDataKey])) {
                        return 'error_not_valid_certData';
                    }
                    break;
                }
            }
        }
        foreach ($certDataKeys as $certDataKey) {
            if (!isset($certificates[$certDataKey]) && $this->_entityController->hasMetadata($certDataKey)) {
                $this->_entityController->removeMetadata($certDataKey);
                $this->_updated = true;
            }
        }

        $parsedMetaData = array_merge($parsedMetaData, array_unique($certificates));
        return false;
    }

    /**
     * @param string $certData the certificate as entered by the user
     * @return bool valid certificate?
     */
    private function _validatePublicCertificate($certData)
    {
        return openssl_pkey_get_public('-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split($certData, 64, PHP_EOL) . '-----END CERTIFICATE-----' . PHP_EOL);
    }

    /**
     * @param string $certData the certificate as entered by the user
     * @return bool valid certificate?
     */
    private function _validatePrivateCertificate($certData)
    {
        return openssl_pkey_get_private('-----BEGIN RSA PRIVATE KEY-----' . PHP_EOL . chunk_split($certData, 64, PHP_EOL) . '-----END RSA PRIVATE KEY-----' . PHP_EOL);
    }

    /**
     * Reparse metadata to correct the contact person metadata
     *
     * @param array $parsedMetadata Array of metadata as returned by SSP
     *
     * @return array Array of metadata
     */
    private function _removeUnusedContacts($parsedMetadata)
    {
        // Janus only support one telephone / emailAddress per contact so use the first
        if (!isset($parsedMetadata['contacts'])) {
            return $parsedMetadata;
        }

        for ($i=0;$i<count($parsedMetadata['contacts']);$i++) {
            if (isset($parsedMetadata['contacts'][$i]['emailAddress'])) {
                $parsedMetadata['contacts'][$i]['emailAddress']
                    = $parsedMetadata['contacts'][$i]['emailAddress'][0];
            }
            if (isset($parsedMetadata['contacts'][$i]['telephoneNumber'])) {
                $parsedMetadata['contacts'][$i]['telephoneNumber']
                    = $parsedMetadata['contacts'][$i]['telephoneNumber'][0];
            }
        }

        return $parsedMetadata;
    }

    /**
     * Removes AssertionConsumerServices and SingleSignOnServices with a non-SAML2 binding type.
     *
     * @param array $parsedMetadata
     * @return mixed
     */
    private function _removeNonSaml2Services(array $parsedMetadata)
    {
        $supportedSamlBindings = array(
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
            'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
            'urn:oasis:names:tc:SAML:2.0:bindings:URI'
        );

        $serviceTypes = array(
            'AssertionConsumerService',
            'SingleSignOnService',
            'ArtifactResolutionService',
            'SingleLogoutService',
        );
        foreach ($serviceTypes as $serviceType) {
            if (empty($parsedMetadata[$serviceType]) || !is_array($parsedMetadata[$serviceType])) {
                continue;
            }

            foreach ($parsedMetadata[$serviceType] as $key => $value) {
                if (isset($value['Binding']) && !in_array($value['Binding'], $supportedSamlBindings)) {
                    unset($parsedMetadata[$serviceType][$key]);
                }
            }
            // Reorder (Note that THIS is the place where we lose all Indexes for IndexedServices)
            $parsedMetadata[$serviceType] = array_values($parsedMetadata[$serviceType]);
        }
        return $parsedMetadata;
    }

    private function startImport()
    {
        $this->resetHasUpdated();
        $this->raiseMemoryLimit();
    }

    private function resetHasUpdated()
    {
        $this->_updated = false;
    }

    /**
     * We raise the memory limit specifically for big metadata providers like edugain.
     */
    private function raiseMemoryLimit()
    {
        $this->_previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');
    }

    /**
     * Reset the memory limit back to it's original value.
     */
    private function resetMemoryLimit()
    {
        ini_set('memory_limit', $this->_previousMemoryLimit);
    }

    /**
     * If a metadata document for a Service Provider specified 'RequestedAttribute' elements, then we
     * add that as an ARP.
     *
     * @param array $parsedMetadata SAMLParser output
     * @return array SAMLParser output without 'attributes'.
     */
    private function _applyRequestedAttributesAsArp(array $parsedMetadata)
    {
        if (!isset($parsedMetadata['attributes'])) {
            return $parsedMetadata;
        }

        $arpAttributes = $this->getAllowedArpAttributes();
        $requestedAttributes = $this->denormalizeAttributes($parsedMetadata['attributes']);

        $arp = array();
        foreach ($requestedAttributes as $requestedAttribute) {
            // Skip attributes not allowed in an ARP.
            if (!in_array($requestedAttribute, $arpAttributes)) {
                continue;
            }

            $arp[$requestedAttribute] = array('*');
        }

        $this->_entityController->setArpAttributes($arp);
        unset($parsedMetadata['attributes']);

        return $parsedMetadata;
    }

    /**
     * Collect the id of the configured attributes we allow in an ARP.
     *
     * @return array<string>
     * @throws Exception
     */
    private function getAllowedArpAttributes()
    {
        $configured_attributes = $this->_config->getValue('attributes');

        $arp_attributes = array();
        foreach ($configured_attributes as $label => $config) {
            $arp_attributes[] = $config['name'];
        }

        return $arp_attributes;
    }

    /**
     * Denormalize attributes so if an SP allows 'urn:mace:dir:attribute-def:cn'
     * then the ARP may also contain 'urn:oid:2.5.4.3'.
     *
     * @param array $attributes
     * @return array
     */
    private function denormalizeAttributes(array $attributes)
    {
        $attributes = $this->denormalizeAttributesWithMap($attributes, 'oid2urn');
        $attributes = $this->denormalizeAttributesWithMap($attributes, 'urn2oid');

        return $attributes;
    }

    /**
     * Use a specific (SSP) attribute map to denormalize attributes.
     *
     * @param array<string> $attributes List of attribute ids to denormalize.
     * @param string        $type       Name / type of the attribute map to load.
     * @return array Denormalized attributes.
     * @throws Exception
     */
    private function denormalizeAttributesWithMap(array $attributes, $type)
    {
        $config = SimpleSAML_Configuration::getInstance();
        $filePath = $config->getPathValue('attributenamemapdir', 'attributemap/') . $type . '.php';

        /**
         * @var array<string,string> $attributemap
         */
        $attributemap = array();
        require $filePath;

        foreach ($attributemap as $from => $to) {
            if (!in_array($from, $attributes)) {
                continue;
            }
            $attributes[] = $to;
        }
        return $attributes;
    }
}
