<?php

class sspmod_janus_REST_Exception_InternalServerError extends Exception
    implements sspmod_janus_REST_HttpError
{
    /**
     * @return int HTTP error code
     */
    public function getHttpErrorCode()
    {
        return 500;
    }

    /**
     * @return string HTTP error message
     */
    public function getHttpErrorMessage()
    {
        return 'Internal Server Error';
    }
}