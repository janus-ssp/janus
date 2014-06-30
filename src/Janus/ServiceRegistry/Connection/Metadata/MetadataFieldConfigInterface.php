<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection\Metadata;

interface MetadataFieldConfigInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getChildren();
}
