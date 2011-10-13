<?php

/**
 * Certificate chain.
 */
class sspmod_janus_OpenSsl_Certificate_Chain
{
    protected $_certificates;

    /**
     * Create a new certificate chain.
     *
     * @param array $certificates
     */
    public function __construct(array $certificates = array())
    {
        $this->_certificates = $certificates;
    }

    /**
     * Add a parent certificate.
     *
     * Note that this does not do any checking!
     *
     * @param sspmod_janus_OpenSsl_Certificate $certificate
     * @return sspmod_janus_OpenSsl_Certificate_Chain
     */
    public function addCertificate(sspmod_janus_OpenSsl_Certificate $certificate)
    {
        array_push($this->_certificates, $certificate);
        return $this;
    }

    /**
     * Get a stack of certificates, top most CA is the last certificate.
     *
     * @return array
     */
    public function getCertificates()
    {
        return $this->_certificates;
    }
}