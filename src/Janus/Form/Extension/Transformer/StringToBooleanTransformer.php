<?php
namespace Janus\ConnectionsBundle\Form\Extension\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a string and a boolean.
 *
 * @author Florian Eckerstorfer <lucas@vanlierop.org>
 */
class StringToBooleanTransformer implements DataTransformerInterface
{
    /**
     * Transforms a string into a Boolean.
     *
     * @param string $value
     *
     * @return string String value.
     *
     * @throws TransformationFailedException If the given value is not a Boolean.
     */
    public function transform($value)
    {
        if (empty($value)) {
            return false;
        }

        if (!is_scalar($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        return ($value == '1') ? true : false;
    }

    /**
     * Transforms a Boolean into a string.
     *
     * @param Boolean $value.
     *
     * @return string value.
     *
     * @throws TransformationFailedException If the given value is not a string.
     */
    public function reverseTransform($value)
    {
        if (!is_bool($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        return $value ? '1' : '0';
    }
}