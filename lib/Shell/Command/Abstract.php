<?php

/**
 * Base class that allows for the execution of a command via the shell.
 *
 * @throws sspmod_janus_Shell_Command_Exception
 */
abstract class sspmod_janus_Shell_Command_Abstract implements sspmod_janus_Shell_Command_Interface
{
    const STDIN_CODE = 0;
    const STDOUT_CODE = 1;
    const STDERR_CODE = 2;

    protected $_redirects;

    /**
     * Unix exit status, 0 is normal, anything else is an error condition.
     *
     * @var int
     */
    protected $_exitStatus;

    /**
     * Output of the command.
     *
     * @var string
     */
    protected $_output;

    /**
     * Errors sent to stdErr as one string.
     *
     * @var string
     */
    protected $_errors;

    /**
     * Build the string with the command that should get executed.
     *
     * @abstract
     * @param array $arguments Arguments for the command, don't forget to use escapeshellarg!
     * @return string
     */
    abstract protected function _buildCommand($arguments = array());

    /**
     * Execute the command,
     * feed it with stdIn and store the stdOut, stdErr and exit status for retrieval later.
     *
     * @throws sspmod_janus_Shell_Command_Exception
     * @param string $stdIn
     * @return sspmod_janus_Shell_Command_Abstract
     */
    public function execute($stdIn = "")
    {
        $command = $this->_buildCommand();
        $command = $this->_suffixRedirects($command);

        $descSpec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'a'), // stderr
        );

        $pipes = array();
        $process = proc_open($command, $descSpec, $pipes);

        if (!is_resource($process)) {
            throw new sspmod_janus_Shell_Command_Exception('Failed to execute command: ' . $command);
        }

        if (fwrite($pipes[0], $stdIn) === FALSE) {
            throw new sspmod_janus_Shell_Command_Exception('Failed to write certificate for pipe.');
        }
        fclose($pipes[0]);

        $output = '';
        $errors = '';
        while (!feof($pipes[1]) && !feof($pipes[2])) {
            $output .= fgets($pipes[1]);
            $errors .= fgets($pipes[2]);
        }
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->_errors = $errors;
        $this->_output = $output;
        $this->_exitStatus = proc_close($process);
        return $this;
    }

    /**
     * Wrapper to enables redirection of errors to output
     *
     * Some programs like for example openssl, detect if called from terminal 
     * or via system call, in the latter case this means error output is not available
     * A workaround in those cases is to redirect errors to output
     *
     * @return void
     */
    public function enableErrorToOutputRedirection()
    {
        $this->_addRedirect(self::STDERR_CODE . '>&' . self::STDOUT_CODE);
    }

    /**
     * Adds redirect
     *
     * This method is intentionally not public (yet) since that require the added complexity of escaping redirects
     *
     * @param   string  $redirect
     * @return  void
     */
    protected function _addRedirect($redirect)
    {
        $this->_redirects[] = $redirect;
    }

    /**
     * Suffixes added redirects to command
     *
     * @param   string    $command
     * @return  string    $suffixedCommand
     */
    protected function _suffixRedirects($command)
    {
        if(!is_array($this->_redirects)) {
            return;
        }

        $suffixedCommand = $command;
        foreach($this->_redirects as $redirect) {
            $suffixedCommand .= ' ' . $redirect;
        }

        return $suffixedCommand;
    }

    /**
     * @return int Exit status, 0 is normal, anything else is error code.
     */
    public function getExitStatus()
    {
        return $this->_exitStatus;
    }

    /**
     * @return string Output string for the command
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * @return string Errors from stdErr
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}