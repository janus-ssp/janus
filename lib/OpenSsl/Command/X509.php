<?php

/**
 * OpenSSL x509 command.
 *
 * From the documentation ():
 * "The x509 command is a multi purpose certificate utility.
 * It can be used to display certificate information, convert certificates to various forms,
 * sign certificate requests like a ``mini CA'' or edit certificate trust settings."
 *
 * Mainly used for conversion of X.509 certificates.
 */
class sspmod_janus_OpenSsl_Command_X509 extends sspmod_janus_Shell_Command_Abstract
{
    const COMMAND = 'openssl x509';
    
    const FORM_PEM = 'PEM';
    const FORM_DER = 'DER';

    /**
     * @var string
     */
    protected $_inFile;

    /**
     * @var string
     */
    protected $_outFile;

    /**
     * @var string
     */
    protected $_inForm;

    /**
     * @var string
     */
    protected $_outForm;

    /**
     * @var bool
     */
    protected $_displayText;

    /**
     * Set OpenSSL to output a certificate to human readable text.
     *
     * @return sspmod_janus_OpenSsl_Command_X509
     */
    public function setDisplayText()
    {
        $this->_displayText = true;
        return $this;
    }

    /**
     * Read a certificate from a file.
     *
     * @param string $filepath Path to the file with certificate.
     * @return sspmod_janus_OpenSsl_Command_X509
     */
    public function setInFile($filepath)
    {
        $this->_inFile = $filepath;
        return $this;
    }

    /**
     * What format OpenSSL can expect the certificate in (given via stdIn or file).
     *
     * Lord knows why OpenSSL can't just detect this.
     *
     * @throws sspmod_janus_OpenSsl_Command_Exception_UnsupportedForm
     * @param string $form Either 'PEM' or 'DER'
     * @return sspmod_janus_OpenSsl_Command_X509
     */
    public function setInForm($form)
    {
        if (!in_array($form, array(self::FORM_PEM, self::FORM_DER))) {
            throw new sspmod_janus_OpenSsl_Command_Exception_UnsupportedForm("Form '$form' is unsupported");
        }

        $this->_inForm = $form;
        return $this;
    }

    /**
     * Output either a 'PEM' or 'DER' certificate.
     *
     * @throws sspmod_janus_OpenSsl_Command_Exception_UnsupportedForm
     * @param string $form Either 'PEM' or 'DER'.
     * @return sspmod_janus_OpenSsl_Command_X509
     */
    public function setOutForm($form)
    {
        if (!in_array($form, array(self::FORM_PEM, self::FORM_DER))) {
            throw new sspmod_janus_OpenSsl_Command_Exception_UnsupportedForm("Form '$form' is unsupported");
        }

        $this->_outForm = $form;
        return $this;
    }

    /**
     * Send the output to a file.
     *
     * @param $file
     * @return sspmod_janus_OpenSsl_Command_X509
     */
    public function setOutFile($file)
    {
        $this->_outFile = $file;
        return $this;
    }

    public function _buildCommand($arguments = array())
    {
        $command = self::COMMAND;
        if ($this->_displayText) {
            $command .= ' -text';
        }
        if ($this->_inForm) {
            $command .= ' -inform ' . $this->_inForm;
        }
        if ($this->_outForm) {
            $command .= ' -outform ' . $this->_outForm;
        }
        if ($this->_inFile) {
            $command .= ' -in ' . $this->_inFile;
        }
        if ($this->_outFile) {
            $command .= ' -out ' . $this->_outFile;
        }
        return $command;
    }
}