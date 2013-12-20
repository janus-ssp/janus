<?php
namespace Janus\Connection;

use Janus\NestedValueSetter;

class NestedCollection
    implements \ArrayAccess
{
    const PATH_SEPARATOR_REGEX = '[.:]';

    /**
     * @var array
     */
    private $items;

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /***
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Turns a flat collection into a nested one.
     *
     * @param array $flatCollection
     * @return NestedCollection
     */
    public static function createFromFlatCollection(array $flatCollection)
    {
        $items = array();
        // @todo inject
        $nestedValueSetter = new NestedValueSetter($items, self::PATH_SEPARATOR_REGEX);
        foreach ($flatCollection as $key => $value) {
            $nestedValueSetter->setValue($key, $value);
        }

        return new self($items);
    }

    /**
     * Turns a nested collection into a flat one.
     *
     * @param array $metadata
     * @return array
     */
    public function flatten()
    {
        $flatCollection = array();
        $this->flattenEntry($flatCollection, $this->items);

        return $flatCollection;
    }

    /**
     * Turns a nested entry of a collection into a flat one recursively.
     *
     * @param array $flatCollection
     * @param array $metadata
     * @param string $parentKey
     */
    public function flattenEntry(
        array &$flatCollection,
        array $metadata,
        &$parentKey = ''
    )
    {
        foreach ($metadata as $key => $value) {
            $newKey = !empty($parentKey) ? $parentKey . ':' : '';
            $newKey .= $key;

            if (is_array($value)) {
                $this->flattenEntry($flatCollection, $value, $newKey);
            } else {
                $flatCollection[$newKey] = $value;
            }
        }
    }

    public function getItems()
    {
        return $this->items;
    }
}