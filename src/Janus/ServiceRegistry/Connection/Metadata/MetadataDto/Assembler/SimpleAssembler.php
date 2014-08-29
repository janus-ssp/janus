<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;

/**
 * Simple Assembler for metadata DTO.
 *
 * Note that we could actually support Nesting here... but we don't because Nesting is a really bad idea in retrospect.
 * Clients are not going to use the Janus data model, instead they pick what they want and put it in their own models.
 * And picking from a nested data structure is much more awkward than from key / value.
 */
class SimpleAssembler implements AssemblerInterface
{
    public function assemble(Collection $metadata)
    {
        $flatMetadata   = $this->collectionToArray($metadata);
        return new MetadataDto($flatMetadata);
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
}