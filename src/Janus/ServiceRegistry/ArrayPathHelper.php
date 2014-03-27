<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry;

use InvalidArgumentException;

class ArrayPathHelper
{
    const DEFAULT_PATH_SEPARATOR_REGEX = '/[:.]/';

    /**
     * @var array
     */
    private $haystack;

    /**
     * @var string
     */
    private $separatorRegexp;

    /**
     * @param array $haystack
     * @param string $separatorRegexp
     */
    public function __construct(array $haystack = array(), $separatorRegexp = self::DEFAULT_PATH_SEPARATOR_REGEX)
    {
        $this->haystack = $haystack;
        $this->separatorRegexp = $separatorRegexp;
    }

    /**
     * Stores value in nested array specified by path
     *
     * @param   string   $path       location split by separator
     * @param   string   $value
     * @return  void
     * @throws \InvalidArgumentException
     */
    public function set($path, $value)
    {
        if (empty($path)) {
            throw new InvalidArgumentException("Path should not be empty");
        }

        if (!is_string($path)) {
            throw new InvalidArgumentException("Path is a '" . gettype($path) . "', expected a string");
        }

        $pathParts = preg_split("/{$this->separatorRegexp}/", $path);
        $target =& $this->haystack;
        do {
            $partName = array_shift($pathParts);

            // Store value if path is found
            if (empty($pathParts)) {
                $target[$partName] = $value;
                return;
            }

            // Get reference to nested child
            if (!array_key_exists($partName, $target)) {
                $target[$partName] = array();
            }
            $target =& $target[$partName];
        } while (true);
    }

    public function getArray()
    {
        return $this->haystack;
    }
}
