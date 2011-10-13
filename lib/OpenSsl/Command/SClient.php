<?php

/**
 * OpenSSL s_client command
 *
 * From the OpenSSL documentation (http://www.openssl.org/docs/apps/s_client.html):
 * "The s_client command implements a generic SSL/TLS client which connects to a remote host using SSL/TLS.
 * It is a very useful diagnostic tool for SSL servers."
 */ 
class sspmod_janus_OpenSsl_Command_SClient extends sspmod_janus_Shell_Command_Abstract
{
    const COMMAND = 'openssl s_client';

    protected $_connectTo;
    protected $_showCerts;
    protected $_certificateAuthorityFile;

    public function setConnectTo($host="localhost", $port=443)
    {
        $this->_connectTo = array(
            'host' => $host,
            'port' => $port,
        );
        return $this;
    }

    public function setShowCerts($showCerts)
    {
        $this->_showCerts = $showCerts;
    }

    public function setCertificateAuthorityFile($file)
    {
        $this->_certificateAuthorityFile = $file;
        return $this;
    }

    public function _buildCommand($arguments = array())
    {
        $command = self::COMMAND;
        if (isset($this->_connectTo)) {
            $command .= " -connect {$this->_connectTo['host']}:{$this->_connectTo['port']}";
        }
        if (isset($this->_showCerts) && $this->_showCerts) {
            $command .= ' -showcerts';
        }
        if (isset($this->_certificateAuthorityFile)) {
            $command .= ' -CAfile ' . escapeshellarg($this->_certificateAuthorityFile);
        }
        return $command;
    }
}
