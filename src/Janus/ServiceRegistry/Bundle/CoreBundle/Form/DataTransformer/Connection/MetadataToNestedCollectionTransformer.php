<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer\Connection;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;

class MetadataToNestedCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $connectionType;

    /**
     * @var ConfigProxy
     */
    protected $janusConfig;

    public function __construct($connectionType, $janusConfig)
    {
        $this->connectionType = $connectionType;
        $this->janusConfig = $janusConfig;
    }

    /**
     * Transforms an nested metadata collection into an array.
     *
     * @param  MetadataDto|null $metadataCollection
     * @return array
     */
    public function transform($metadataCollection = null)
    {
        if (null === $metadataCollection) {
            return array();
        }

        return $metadataCollection->getItems();
    }

    /**
     * Transforms a nested array to a nested collection.
     *
     * @param  array $metadata
     *
     * @return MetadataDto|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($metadata = null)
    {
        if (!$metadata) {
            return null;
        }

        return new MetadataDto($metadata);
    }
}
