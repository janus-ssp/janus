<?php

/**
 * Factory for the conversion of JANUS certData fields to Certificate objects.
 */ 
class sspmod_janus_CertificateFactory
{
    /**
     * Create a Certificate object based on PEM encoded concatenated certificate data.
     *
     * @static
     * @throws sspmod_janus_Exception_NoCertData
     * @throws sspmod_janus_OpenSsl_Certificate_Exception_NotAValidPem
     * @param string $certData Certificate PEM encoded data in a single string
     * @return sspmod_janus_OpenSsl_Certificate
     */
    public static function create($certData)
    {
        $pem = trim($certData);
        if ($pem==="") {
            throw new sspmod_janus_Exception_NoCertData();
        }

        // Strip out possible newlines
        $pem = str_replace("\n", "", $pem);
        $pem = str_replace("\r", "", $pem);

        // Split it into chunks of 64 characters
        $pem = chunk_split($pem, 64, "\r\n");

        // remove the last \n character
        $pem = substr($pem, 0, -1);

        // Add header and footer
        if(strpos($pem, '-----BEGIN CERTIFICATE-----') === FALSE) {
            $pem = '-----BEGIN CERTIFICATE-----' . PHP_EOL . $pem . PHP_EOL . '-----END CERTIFICATE-----' . PHP_EOL;
        }
        return new sspmod_janus_OpenSsl_Certificate($pem);
    }
}
