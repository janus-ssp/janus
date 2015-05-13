<?php

namespace Janus\ServiceRegistry;

use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Importer\ArpMapper;
use Janus\ServiceRegistry\Importer\MetadataFieldMapperInterface;
use Janus\ServiceRegistry\Saml2\EntityDescriptorFactory;
use SAML2_XML_md_EntityDescriptor;

class SamlMdUnmarshaller
{
    private $entityId;

    /**
     * @var EntityDescriptorFactory
     */
    private $entityDescriptorFactory;

    private $logger;

    /**
     * @var ArpMapper
     */
    private $arpMapper;

    /**
     * @var MetadataFieldMapperInterface[]
     */
    private $commands;

    private $connection;

    public function unmarshall($xml)
    {
        $entityDescriptor = $this->entityDescriptorFactory->createFromMetadataXml($xml, $this->entityId);
        if (!$entityDescriptor) {
            return NULL;
        }

        $newArp = $this->arpMapper->mapToArp($entityDescriptor);
        $newMetadata = $this->mapEntityDescriptor($entityDescriptor);

        $revision = new Revision();
        return $revision;
    }

    public function mapEntityDescriptor(SAML2_XML_md_EntityDescriptor $entity) {
        $metadataFields = array();

        foreach ($this->commands as $command) {
            $commandFields = $command->import($entity);
            $metadataFields = array_merge($metadataFields, $commandFields);
        }

        return $metadataFields;
    }
}
