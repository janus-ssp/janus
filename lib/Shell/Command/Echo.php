<?php

/**
 * Demo implementation of shell command pattern.
 */
class sspmod_janus_Shell_Command_Echo extends sspmod_janus_Shell_Command_Abstract
{
    const COMMAND = 'echo';

    public function _buildCommand($arguments = array())
    {
        return self::COMMAND . ' ' . escapeshellarg($arguments[0]);
    }
}