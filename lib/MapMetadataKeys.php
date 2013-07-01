<?php

class sspmod_janus_MapMetadataKeys implements sspmod_janus_Command
{
    private static $instance;

    private $mapping;

    private function __construct()
    {
        $this->mapping = array();
    }

    private function __clone()
    {
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

    public function exec(array $md)
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
