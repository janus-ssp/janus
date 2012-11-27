<?php

interface sspmod_janus_REST_HttpError
{
    /**
     * @return int HTTP error code
     */
    public function getHttpErrorCode();

    /**
     * @return string HTTP error message
     */
    public function getHttpErrorMessage();
}
