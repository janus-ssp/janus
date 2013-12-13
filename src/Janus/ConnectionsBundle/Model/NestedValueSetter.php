<?php
namespace Janus\ConnectionsBundle\Model;

class NestedValueSetter
{
    /**
     * @var array
     */
    private $haystack;

    /**
     * @var string
     */
    private $separator;

    /**
     * @param array $haystack
     * @param string $separator
     */
    public function __construct(array &$haystack, $separator = '\.')
    {
        $this->haystack =& $haystack;
        $this->separator = $separator;
    }

    /**
     * Stores value in nested array specified by path
     *
     * @param   string   $path       location split by separator
     * @param   string   $value
     * @return  void
     */
    public function setValue($path, $value)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Path should not be empty");
        }

        if (!is_string($path)) {
            throw new \InvalidArgumentException("Path is a '" . gettype($path) . "', expected a string");
        }

        $pathParts = preg_split("/{$this->separator}/", $path);
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
}