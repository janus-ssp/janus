<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistryBundle\Form\DataTransformer\Connection;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Janus\ServiceRegistry\Connection\NestedCollection;

class MetadataToNestedCollectionTransformer implements DataTransformerInterface
{
    /**
     * Transforms an nested metadata collection into an array.
     *
     * @param  NestedCollection|null $metadataCollection
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
     * @param  array metatadata
     *
     * @return NestedCollection|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($metadata = null)
    {
        if (!$metadata) {
            return null;
        }

        return new NestedCollection($metadata);
    }
}