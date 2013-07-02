<?php

class sspmod_janus_Metadata_Converter_Command_MapKeysCommand implements sspmod_janus_Metadata_Converter_Command_CommandInterface
{
    private static $instance;

    private $mapping;

    public function __construct()
    {
        $this->mapping = array();
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function convert(array $md)
    {
        foreach ($md as $k => $v) {
            if (array_key_exists($k, $this->mapping)) {
                $md[$this->mapping[$k]] = $v;
                unset($md[$k]);
            }
        }

        return $md;
    }

}
