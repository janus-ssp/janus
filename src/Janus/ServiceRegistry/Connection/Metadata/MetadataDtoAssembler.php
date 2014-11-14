<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\ArrayPathHelper;

class MetadataDtoAssembler
{
    /**
     * Turns a flat collection into a nested one.
     *
     * @param array $flatCollection
     * @param MetadataDefinitionHelper $metadataDefinitionHelper
     * @return MetadataDto
     */
    public function createFromFlatArray(array $flatCollection, MetadataDefinitionHelper $metadataDefinitionHelper)
    {
        $flatCollection = $metadataDefinitionHelper->castData($flatCollection);

        $arrayPathHelper = new ArrayPathHelper();
        foreach ($flatCollection as $key => $value) {
            $arrayPathHelper->set($key, $value);
        }
        $items = $arrayPathHelper->getArray();

        return new MetadataDto($items);
    }
}