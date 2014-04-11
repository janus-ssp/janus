<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\ArrayPathHelper;

class MetadataDto
    implements \ArrayAccess, \Iterator
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var \ArrayIterator
     */
    private $itemsIterator;

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
        $this->itemsIterator = new \ArrayIterator($this->items);
    }

    /**
     * Turns a flat collection into a nested one.
     *
     * @param array $flatCollection
     * @return MetadataDto
     */
    public static function createFromFlatArray(array $flatCollection)
    {
        $arrayPathHelper = new ArrayPathHelper();
        foreach ($flatCollection as $key => $value) {
            $arrayPathHelper->set($key, $value);
        }
        $items = $arrayPathHelper->getArray();

        return new self($items);
    }

    /**
     * Turns a nested collection into a flat one.
     *
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->itemsIterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->itemsIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->itemsIterator->key();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->itemsIterator->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->itemsIterator->rewind();
    }
}
