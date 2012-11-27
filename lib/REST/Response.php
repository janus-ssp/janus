<?php

class sspmod_janus_REST_Response
{
    /**
     * @var array $_data
     */
    protected $_data = null;

    /**
     * @var array $_headers
     */
    protected $_headers = null;

    /**
     * @var int $_httpCode
     */
    protected $_httpCode = null;

    /**
     * @var string $_httpMessage
     */
    protected $_httpMessage = null;

    /**
     * @param array $data
     * @param int $httpCode
     * @param string $httpMessage
     */
    public function __construct(array $data = array(), array $headers = array(),
        $httpCode = 200, $httpMessage = 'OK')
    {
        $this->_data        = $data;
        $this->_headers     = $headers;
        $this->_httpCode    = (int)$httpCode;
        $this->_httpMessage = (string)$httpMessage;
    }

    /**
     * @param array $data Response data
     * @return sspmod_janus_REST_Response
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * @param int $httpCode
     * @param string $httpMessage
     * @return sspmod_janus_REST_Response
     */
    public function setHttpStatus($httpCode, $httpMessage)
    {
        $this->_httpCode    = (int)$httpCode;
        $this->_httpMessage = (string)$httpMessage;

        return $this;
    }

    /**
     * @param Exception|sspmod_janus_REST_HttpError $fault
     * @return sspmod_janus_REST_Response
     */
    public function setError(Exception $fault)
    {
        $classParts = explode('_', get_class($fault));
        $this->setData(
            array(
                'error'    => array_pop($classParts),
                'message'  => $fault->getMessage(),
            )
        );

        // default to HTTP 500 on generic exceptions
        $this->setHttpStatus(
            500, 'Internal Server Error'
        );

        // set custom HTTP code when specified
        if ($fault instanceof sspmod_janus_REST_HttpError) {
            $this->setHttpStatus(
                $fault->getHttpErrorCode(), $fault->getHttpErrorMessage()
            );
        }

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return sspmod_janus_REST_Response
     */
    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;

        return $this;
    }

    /**
     * Returns json encoded response body
     *
     * @return string
     */
    public function render()
    {
        return json_encode(
            $this->_data
        );
    }

    /**
     * Send the HTTP response
     *
     * @return int 1, always
     */
    public function send()
    {
        header("HTTP/1.0 {$this->_httpCode} {$this->_httpMessage}");

        foreach ($this->_headers as $name => $value) {
            header("$name: $value");
        }

        return print $this->render();
    }

    /**
     * Proxy to render()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Send message to Logger
     */
    public function logResponse()
    {
        $user = sspmod_janus_REST_Mapper_Abstract::getUserController()->getUser();
        $userName = ($user)
            ? $user->getUserid()
            : 'none';

        SimpleSAML_Logger::error(sprintf(
            'Error handling request: %s %s [USER: \'%s\' BODY: \'%s\']',
            $this->_httpCode,
            $this->_httpMessage,
            $userName,
            var_export($this->_data, true)
        ));
    }
}
