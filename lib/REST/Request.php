<?php

class sspmod_janus_REST_Request
{
    /**
     * Supported HTTP methods (can be limited by the
     *  requested resource)
     */
    const METHOD_GET    = 'GET';
    const METHOD_PUT    = 'PUT';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD   = 'HEAD';
    const METHOD_TRACE  = 'TRACE';

    /**
     * @var string $_method
     */
    protected $_method = null;

    /**
     * @var string $_resource
     */
    protected $_resource = null;

    /**
     * @var array $_parameters
     */
    protected $_parameters = array();

    /**
     * @param string $method HTTP method
     * @param string $resource Entity ID
     * @param array $request Copy of request superglobal
     */
    public function __construct($method, $resource, array $parameters)
    {
        $this->_method     = (string)$method;
        $this->_resource   = (string)$resource;
        $this->_parameters = $parameters;
    }

    /**
     * Returns HTTP method (see defined class constants)
     *
     * @return string
     */
    public function getMethod()
    {
        // always use uppercase method names
        $method = strtoupper($this->_method);

        // throw exception on invalid method
        $reflection = new ReflectionObject($this);
        if (!$reflection->hasConstant('METHOD_' . $method)) {
            throw new sspmod_janus_REST_Exception_NotImplemented(
                'This API does not support the requested method'
            );
    }

        return $method;
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Returns the request params (assumed copy of request superglobal)
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Returns one request parameter
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return (isset($this->_parameters[$name]))
            ? $this->_parameters[$name]
            : $default;
    }

    /**
     * Returns one request header
     *
     * @param string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        $headers = $this->getHeaders();

        return isset($headers[$name]) ? $headers[$name] : null;
    }

    /**
     * Returns all request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = apache_request_headers();

        // should happen when not running on apache
        if (empty($headers)) {
            throw new sspmod_janus_REST_Exception_InternalServerError(
                'Unable to read request headers, please add support '
                . 'for non-apache web servers.'
            );
        }

        return $headers;
    }

    /**
     * Set one request parameter
     *
     * @param string $name
     * @param mixed $value
     * @return sspmod_janus_REST_Request
     */
    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;

        return $this;
    }

    /**
     * Parses request bodies
     *
     * @return \sspmod_janus_REST_Request
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    public function initParameters()
    {
        // try to parse request body
        $body = file_get_contents('php://input');
        if ($body) {
            $decoded = json_decode($body);
            if (!$decoded) {
                throw new sspmod_janus_REST_Exception_BadRequest(
                    'Non-JSON request body detected'
                );
            }

            $this->_parameters = array_merge(
                $this->_parameters, (array)$decoded
            );
        }

        return $this;
    }

    /**
     * Test existence of parameter
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function hasParameter($name)
    {
        return (array_key_exists($name, $this->_parameters));
    }

    /**
     * Collection request? /idp or /idp/5/arp
     *
     * @return bool
     */
    public function isCollection()
    {
        $parts = $this->getTargetEntity();

        return empty($parts['id']);
    }

    /**
     * Has target entity?
     *  false: /
     *  true:  /idp
     *
     * @return bool
     */
    public function hasTargetEntity()
    {
        $parts = $this->getTargetEntity();

        return !empty($parts['name']);
    }

    /**
     * We support no nesting, return entity name and if
     * of URI
     *
     * @return array
     */
    public function getTargetEntity()
    {
        $resource = trim($this->_resource, '/');

        $parts = explode('/', $resource, 2);

        return array(
            'name' => isset($parts[0])
                ? $parts[0] : null,
            'id' => isset($parts[1])
                ? $parts[1] : null,
        );
    }

    /**
     * Send message to Logger
     */
    public function logRequest()
    {
        SimpleSAML_Logger::error(sprintf(
            'Handling %s %s [PARAMS: \'%s\'] [HEADERS: \'%s\']',
            $this->getMethod(),
            $this->_resource,
            var_export($this->getParameters(), true),
            var_export($this->getHeaders(), true)
        ));
    }
}
