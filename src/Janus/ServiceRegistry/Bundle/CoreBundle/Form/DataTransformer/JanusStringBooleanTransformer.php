<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JanusStringBooleanTransformer implements DataTransformerInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $values
     * @return mixed|string
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($values)
    {
        if ($values === null) {
            return array();
        }

        if (is_bool($values)) {
            return array($this->name => $values);
        }

        if (is_string($values)) {
            return array($this->name => $this->transformStringToBool($values));
        }

        if (!is_array($values)) {
            throw new TransformationFailedException(
                'Value is not an array:' . var_export($values, true)
            );
        }

        $newValues = array();
        foreach ($values as $key => $value) {
            if (is_bool($value) || $key !== $this->name) {
                $newValues[$key] = $value;
                continue;
            }

            if (!is_string($value)) {
                throw new TransformationFailedException(
                    'Does not contain all string values: ' . var_export($values, true)
                );
            }

            $newValues[$key] = $this->transformStringToBool($value);
        }

        return $newValues;
    }

    /**
     * @param mixed $values
     * @return mixed|null|string
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform($values)
    {
        if (empty($values)) {
            return null;
        }

        if (is_bool($values)) {
            return $this->transformBoolToString($values);
        }

        if (!is_array($values)) {
            throw new TransformationFailedException(
                'Value is not a bool or an array:' . var_export($values, true)
            );
        }

        $newValues = array();
        foreach ($values as $key => $value) {
            if ($key !== $this->name) {
                $newValues[$key] = $value;
                continue;
            }

            if (!is_bool($value)) {
                throw new TransformationFailedException('Value should be a boolean: ' . var_export($values, true));
            }

            $newValues[$key] = $this->transformBoolToString($value);
        }

        return $newValues;
    }

    /**
     * @param $values
     * @return string
     */
    protected function transformBoolToString($values)
    {
        return ($values ? '1' : '0');
    }

    /**
     * @param $value
     * @return bool
     */
    protected function transformStringToBool($value)
    {
        return ($value === '1' ? true : false);
    }
}
