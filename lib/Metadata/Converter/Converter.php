<?php

class sspmod_janus_Metadata_Converter_Converter
{
    private static $instance;

    private $commands;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            $config = SimpleSAML_Configuration::getInstance();
            $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
            self::$instance = new self();
            self::$instance->registerCommand(sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand::getInstance());
            self::$instance->registerCommand(sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand::getInstance());
            $mapping = $janusConfig->getArray('md.mapping', array());
            $mapMetadataKeys = sspmod_janus_Metadata_Converter_Command_MapKeysCommand::getInstance();
            $mapMetadataKeys->setMapping($mapping);
            self::$instance->registerCommand($mapMetadataKeys);
        }

        return self::$instance;
    }

    public function registerCommand(sspmod_janus_Metadata_Converter_Command_CommandInterface $command)
    {
        $this->commands[] = $command;
    }

    public function execute(array $collection)
    {
        foreach ($this->commands as $command) {
            $collection = $command->convert($collection);
        }

        return $collection;
    }
}
