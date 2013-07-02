<?php

class sspmod_janus_Metadata_Converter_Converter
{
    /** @var sspmod_janus_Metadata_Converter_Converter */
    private static $instance;

    /** @var array<sspmod_janus_Metadata_Converter_Command_CommandInterface> */
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
            self::$instance->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand());
            self::$instance->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand());
            $mapping = $janusConfig->getArray('md.mapping', array());
            $mapKeysCommand = new sspmod_janus_Metadata_Converter_Command_MapKeysCommand();
            $mapKeysCommand->setMapping($mapping);
            self::$instance->registerCommand($mapKeysCommand);
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
