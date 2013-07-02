<?php

class sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand implements sspmod_janus_Metadata_Converter_Command_CommandInterface
{
    /** @var sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand */
    private static $instance;

    /** @var string */
    private $separator;

    public function __construct()
    {
        $this->separator = ":";
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setSeparator($separator)
    {
        $this->separator = strval($separator);
    }

    /**
     * Flatten an array to only one level using a separator
     *
     * @param array $array The array to be flattened
     *
     * @return array The flattened array to one level
     */
    public function convert(array $array)
    {
        $result = array();
        $stack = array();
        array_push($stack, array("", $array));

        while (count($stack) > 0) {
            list($prefix, $array) = array_pop($stack);

            foreach ($array as $key => $value) {
                $new_key = $prefix . strval($key);

                if (is_array($value)) {
                    array_push($stack, array($new_key . $this->separator, $value));
                } else {
                    $result[$new_key] = $value;
                }
            }
        }

        return $result;
    }

}
