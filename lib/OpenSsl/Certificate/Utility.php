<?php

/**
 * Utility class dealing with certificates.
 */ 
class sspmod_janus_OpenSsl_Certificate_Utility
{
    /**
     * Look for PEM encoded certs in text (like Mozillas CA bundle).
     *
     * @static
     * @param string $text
     * @return array Certificates found (array of sspmod_janus_OpenSsl_Certificate objects)
     */
    public static function getCertificatesFromText($text)
    {
        $inputLines = explode(PHP_EOL, $text);
        $certificatesFound = array();
        $recording = false;
        $certificate = "";
        foreach ($inputLines as $inputLine) {
            if (trim($inputLine) === "-----BEGIN CERTIFICATE-----") {
                $certificate = "";

                $recording = true;
            }

            if ($recording) {
                $certificate .= $inputLine . PHP_EOL;
            }

            if (trim($inputLine) === "-----END CERTIFICATE-----") {
                $certificate = new sspmod_janus_OpenSsl_Certificate($certificate);
                $certificatesFound[$certificate->getSubjectDN()] = $certificate;
                $recording = false;
            }
        }
        return $certificatesFound;
    }
}
