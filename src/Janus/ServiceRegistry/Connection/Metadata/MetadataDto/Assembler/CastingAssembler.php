<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto\MetadataDefinitionHelper;

class CastingAssembler extends SimpleAssembler
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
        $flatMetadata = $this->collectionToArray($metadata);
        $castedMetadata = $this->metadataDefinitionHelper->castData($flatMetadata);
        $nestedMetadata = $this->nestMetadata($castedMetadata);
        return $nestedMetadata;
    }
}