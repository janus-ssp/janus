<?php

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\ArrayPathHelper;

class MetadataTreeBuilder
{
    /**
     * Turns a flat collection into a nested one.
     *
     * @param array $flatCollection
     * @param MetadataDefinitionHelper $metadataDefinitionHelper
     * @return array
     */
    public function createFromFlatArray(array $flatCollection, MetadataDefinitionHelper $metadataDefinitionHelper, $connectionType)
    {
        $flatCollection = $metadataDefinitionHelper->castData($flatCollection, $connectionType);

        $arrayPathHelper = new ArrayPathHelper();
        foreach ($flatCollection as $key => $value) {
            $arrayPathHelper->set($key, $value);
        }
        $items = $arrayPathHelper->getArray();

        return $items;
    }
}