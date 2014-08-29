<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;
use Janus\ServiceRegistry\ArrayPathHelper;
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
        return $this->createFromFlatArray(
            parent::assemble($metadata)
        );
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