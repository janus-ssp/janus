<?php

namespace Janus\ServiceRegistry\Importer;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Psr\Log\LoggerInterface;
use SAML2_XML_ds_X509Certificate;
use SAML2_XML_ds_X509Data;
use SAML2_XML_md_EntityDescriptor;

class CertDataMetadataFieldMapper implements MetadataFieldMapperInterface
{
    const FIELD0 = 'certData';
    const FIELD1 = 'certData2';
    const FIELD2 = 'certData3';

    /**
     * @var ConfigProxy
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SAML2_XML_md_EntityDescriptor $entity
     * @return array
     */
    public function import(SAML2_XML_md_EntityDescriptor $entity)
    {
        $encryptionIsEnabled = $this->config->getBoolean('encryption.enable');

        $certDataStrings = $this->mapEntityDescriptorToCertDataStrings($entity, $encryptionIsEnabled);

        $certDataStrings = $this->filterInvalidCertDataStrings($certDataStrings);

        return $this->mapCertDataStringsToMetadataFields($certDataStrings);
    }

    /**
     * @param SAML2_XML_md_EntityDescriptor $entity
     * @param $encryptionIsEnabled
     * @return string[]
     */
    private function mapEntityDescriptorToCertDataStrings(SAML2_XML_md_EntityDescriptor $entity, $encryptionIsEnabled)
    {
        $certDataStrings = array();
        /** @var \SAML2_XML_md_RoleDescriptor $role */
        foreach ($entity->RoleDescriptor as $role) {
            foreach ($role->KeyDescriptor as $key) {
                if ($key->use === 'encryption' && !$encryptionIsEnabled) {
                    continue;
                }
                foreach ($key->KeyInfo->info as $keyInfoElement) {
                    if (!$keyInfoElement instanceof SAML2_XML_ds_X509Data) {
                        continue;
                    }
                    foreach ($keyInfoElement->data as $dataElement) {
                        if (!$dataElement instanceof SAML2_XML_ds_X509Certificate) {
                            continue;
                        }
                        $certDataStrings[] = $dataElement->certificate;
                    }
                }
            }
        }
        return $certDataStrings;
    }

    private function cleanCertDataString($certData)
    {
        return str_replace(array(" ", "\r\n", "\n", "\r", "\t", "\x09"), '', $certData);
    }

    /**
     * @param string $certData the certificate as entered by the user
     * @return bool valid certificate?
     */
    private function validatePublicCertificate($certData)
    {
        $keyResource = openssl_pkey_get_public(
            '-----BEGIN CERTIFICATE-----' . PHP_EOL
            . chunk_split($certData, 64, PHP_EOL)
            . '-----END CERTIFICATE-----' . PHP_EOL
        );
        return (bool) $keyResource;
    }

    /**
     * @param $certDataStrings
     * @return string[]
     */
    private function filterInvalidCertDataStrings($certDataStrings)
    {
        $processed = array();
        foreach ($certDataStrings as $certDataString) {
            $certDataString = $this->cleanCertDataString($certDataString);

            if (!$this->validatePublicCertificate($certDataString)) {
                $this->logger->notice('Import: skipping invalid certificate: ' . $certDataString);
                continue;
            }

            $processed[] = $certDataString;
        }
        return $processed;
    }

    /**
     * @param $certDataStrings
     * @return array
     */
    private function mapCertDataStringsToMetadataFields($certDataStrings)
    {
        $metadataFields = array();

        if (empty($certDataStrings)) {
            return $metadataFields;
        }
        $metadataFields[self::FIELD0] = $certDataStrings[0];

        if (empty($certDataStrings[1])) {
            return $metadataFields;
        }
        $metadataFields[self::FIELD1] = $certDataStrings[1];

        if (empty($certDataStrings[2])) {
            return $metadataFields;
        }
        $metadataFields[self::FIELD2] = $certDataStrings[2];

        return $metadataFields;
    }
}
