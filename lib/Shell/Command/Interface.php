<?php

/**
 * Interface for a shell command.
 */
interface sspmod_janus_Shell_Command_Interface {
    /**
     * @abstract
     * @param string $stdIn
     * @return sspmod_janus_Shell_Command_Interface
     */
    public function execute($stdIn = "");
    public function getExitStatus();
    public function getOutput();
    public function getErrors();
}