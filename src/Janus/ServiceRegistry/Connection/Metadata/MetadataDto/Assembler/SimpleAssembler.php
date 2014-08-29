<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;

class SimpleAssembler implements AssemblerInterface
{
    public function assemble(Collection $metadata)
    {
        /** @var Metadata $metadataRecord */
        $flatMetadata = array();
        foreach ($metadata as $metadataRecord) {
            $flatMetadata[$metadataRecord->getKey()] = $metadataRecord->getValue();
        }
        return $flatMetadata;
    }
}