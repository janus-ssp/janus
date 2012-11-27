<?php

class sspmod_janus_REST_Exception_BadRequest extends Exception
    implements sspmod_janus_REST_HttpError
{
    /**
     * @return int HTTP error code
     */
    public function getHttpErrorCode()
    {
        return 400;
    }

    /**
     * @return string HTTP error message
     */
    public function getHttpErrorMessage()
    {
        return 'Bad Request';
    }
}