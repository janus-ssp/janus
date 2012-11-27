<?php

class sspmod_janus_REST_Exception_NotImplemented extends Exception
    implements sspmod_janus_REST_HttpError
{
    /**
     * @return int HTTP error code
     */
    public function getHttpErrorCode()
    {
        return 501;
    }

    /**
     * @return string HTTP error message
     */
    public function getHttpErrorMessage()
    {
        return 'Not Implemented';
    }
}