<?php
class sspmod_janus_REST_Request
{
    // Params from request (DIRTY)
    private $_raw_data = array();
    private $_request_vars = array();
    private $_signature = null;
    private $_key = null;
    private $_method = null;

    public function __construct() {}

    public function setRawdata($data)
    {
        $this->_raw_data = $data;
    }

    public function getRawdata()
    {
        return $this->_raw_data;
    }

    public function setSignature($signature)
    {
        $this->_signature = $signature;
    }

    public function getSignature()
    {
        return $this->_signature;
    }

    public function setKey($key)
    {
        $this->_key = $key;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function setRequestVars($request_vars)
    {
        $this->_request_vars = $request_vars;
    }

    public function getRequestVars()
    {
        return $this->_request_vars;
    }
}
