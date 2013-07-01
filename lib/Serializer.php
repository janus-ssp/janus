<?php

class sspmod_janus_Serializer
{

    private static $instance;

    private $commands;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(array $mapping)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
            self::$instance->registerCommand(sspmod_janus_FlattenMetadataValues::getInstance());
            self::$instance->registerCommand(sspmod_janus_FlattenMetadataKeys::getInstance());
            $mapMetadataKeys = sspmod_janus_MapMetadataKeys::getInstance();
            $mapMetadataKeys->setMapping($mapping);
            self::$instance->registerCommand($mapMetadataKeys);
        }

        return self::$instance;
    }

    public function registerCommand(sspmod_janus_Command $command)
    {
        $this->commands[] = $command;
    }

    public function exec(array $collection)
    {
        foreach ($this->commands as $command) {
            $collection = $command->exec($collection);
        }

        return $collection;
    }
}
