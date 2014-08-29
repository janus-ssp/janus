<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Janus\ServiceRegistry\ArrayPathHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;

class MetadataDtoAssembler
{
    /**
     * @var MetadataDefinitionHelper
     */
    private $metadataDefinitionHelper;

    /**
     * @param $metadataDefinitionHelper
     */
    public function __construct(MetadataDefinitionHelper $metadataDefinitionHelper)
    {
        $this->metadataDefinitionHelper = $metadataDefinitionHelper;
    }

    /**
     * Turn a Collection of Metadata entities into a MetadataDto.
     *
     * @param Collection $metadata
     * @return MetadataDto
     */
    public function assemble(Collection $metadata)
    {
        /** @var Metadata $metadataRecord */
        $flatMetadata = array();
        foreach ($metadata as $metadataRecord) {
            $flatMetadata[$metadataRecord->getKey()] = $metadataRecord->getValue();
        }

        return $this->createFromFlatArray($flatMetadata);
    }

    /**
     * Turns a flat array into a MetadataDto.
     *
     * @param array $flatCollection
     * @return MetadataDto
     */
    private function createFromFlatArray(array $flatCollection)
    {
        $flatCollection = $this->metadataDefinitionHelper->castData($flatCollection);

        $arrayPathHelper = new ArrayPathHelper();
        foreach ($flatCollection as $key => $value) {
            $arrayPathHelper->set($key, $value);
        }
        $items = $arrayPathHelper->getArray();

        return new MetadataDto($items);
    }
}