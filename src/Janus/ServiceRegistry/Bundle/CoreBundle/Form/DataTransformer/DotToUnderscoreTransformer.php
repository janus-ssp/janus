<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DotToUnderscoreTransformer implements DataTransformerInterface
{
    private $reversed = false;

    public function __construct($reversed = false)
    {
        $this->reversed = (bool) $reversed;
    }

    /**
     * @param mixed $values
     * @return mixed|string
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($values)
    {
        return $this->doTransform($values, $this->reversed);
    }

    /**
     * @param mixed $values
     * @return mixed|string
     * @throws \RuntimeException
     */
    public function reverseTransform($values)
    {
        return $this->doTransform($values, !$this->reversed);
    }

    private function doTransform($values, $reversed = false)
    {
        if (empty($values)) {
            return $values;
        }

        if (!is_array($values)) {
            throw new \RuntimeException('Values to transform are not in array format: ' . var_export($values, true));
        }

        $from = $reversed ? '_' : '.';
        $to   = $reversed ? '.' : '_';
        $newValues = array();
        foreach ($values as $attributeName => $attributeValues) {
            $newValues[str_replace($from, $to, $attributeName)] = $attributeValues;
        }
        return $newValues;
    }
}
