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
        $pathParts = preg_split("/{$this->separator}/", $path);
        $target =& $this->haystack;
        while ($partName = array_shift($pathParts)) {
            // Store value if path is found
            if (empty($pathParts)) {
                return $target[$partName] = $value;
            }

            // Get reference to nested child
            if (!array_key_exists($partName, $target)) {
                $target[$partName] = array();
            }
            $target =& $target[$partName];
        }
    }
}