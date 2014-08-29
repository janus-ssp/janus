<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;
use Janus\ServiceRegistry\ArrayPathHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;

class SimpleAssembler implements AssemblerInterface
{
    public function assemble(Collection $metadata)
    {
        $flatMetadata   = $this->collectionToArray($metadata);
        $nestedMetadata = $this->nestMetadata($flatMetadata);
        return $nestedMetadata;
    }

    protected function collectionToArray(Collection $metadata)
    {
        $flatMetadata = array();
        foreach ($metadata as $metadataRecord) {
            /** @var Metadata $metadataRecord */
            $flatMetadata[$metadataRecord->getKey()] = $metadataRecord->getValue();
        }
        return $flatMetadata;
    }

    protected function nestMetadata($flatMetadata)
    {
        $arrayPathHelper = new ArrayPathHelper();
        foreach ($flatMetadata as $key => $value) {
            $arrayPathHelper->set($key, $value);
        }
        $items = $arrayPathHelper->getArray();

        return new MetadataDto($items);
    }
}