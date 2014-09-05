<?php

class sspmod_janus_Metadata_Converter_Converter
{
    /** @var sspmod_janus_Metadata_Converter_Converter */
    private static $instance;

    /** @var sspmod_janus_Metadata_Converter_Command_CommandInterface[] */
    private $commands;

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
