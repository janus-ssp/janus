<?php

class sspmod_janus_REST_Server
{
    /**
     * List of supported entities
     */
    protected $_entityTypes = array(
         'user', 'idp', 'sp', 'idp-metadata', 'sp-metadata', 'arp',
    );

    /**
     * @var sspmod_janus_REST_Request $_request
     */
    protected $_request = null;

    /**
     * @var sspmod_janus_REST_Request $_response
     */
    protected $_response = null;

    /**
     * @param sspmod_janus_REST_Request $request
     * @param sspmod_janus_REST_Response $response
     */
    public function __construct(
        sspmod_janus_REST_Request $request,
        sspmod_janus_REST_Response $response
    ) {
        $this->_request  = $request;
        $this->_response = $response;
    }

    /**
     * @return sspmod_janus_REST_Server
     */
    public function handle()
    {
        try {
            // parse request  body
            $this->_request->initParameters();

            // log this request
            if (!$this->_request->isGet()) {
                $this->_request->logRequest();
            }

            // authenticate & authorize
            $this->_login();

            // execute request
            $result = $this->_executeRequest();
            if ($result) {
                $this->_response->setData(array(
                    'result' => $result,
                ));
            }

        } catch (Exception $e) {
            // set error data on response object
            $this->_response->setError($e);

            // request already logged for !GET
            if ($this->_request->isGet()) {
                $this->_request->logRequest();
            }

            // log this error
            $this->_response->logResponse();
        }

        return $this;
    }

    /**
     * @param string $type
     * @return string
     */
    public function isSupportedEntityType($type)
    {
        return in_array($type, $this->_entityTypes);
    }

    /**
     * HTTP basic authentication
     *
     * @return sspmod_janus_REST_Server
     */
    protected function _login()
    {
        $manager = sspmod_janus_REST_Mapper_Abstract::getPermissionManager($this->_request);

        $user = $manager->authenticate();

        if (!$manager->authorize($user)) {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'You are not sufficiently authorized'
            );
        }

        return $this;
    }

    /**
     * Call mapper and return response
     *
     * @return array
     */
    protected function _executeRequest()
    {
        // handle " /"
        if (!$this->_request->hasTargetEntity()) {
            if (!$this->_request->isGet()) {
                throw new sspmod_janus_REST_Exception_NotImplemented(
                    'Only GET is implemented on /'
                );
            }

            return $this->_getRootCollectionResources();
        }

        $target = $this->_request->getTargetEntity();

        // check valid id/method, return collection
        if ($this->_request->isCollection()) {
            $mapper = $this->_createMapper($target['name']);

            // create new resource on collection
            if ($this->_request->isPut()) {
                return array($mapper->put());
            }

            if (!$this->_request->isGet()) {
                throw new sspmod_janus_REST_Exception_NotImplemented(
                    'Collections only support GET (list) or PUT (create)'
                );
            }

            return $this->_createMapper($target['name'])
                ->getCollection();
        }

        return array_filter(array(
            $this->_callMapper(
                $target, $this->_request->getMethod()
            )
        ));
    }

    /**
     * @param array $target
     * @param string $method
     * @return sspmod_janus_Mapper
     */
    protected function _callMapper(array $target, $method = 'GET')
    {
        // create mapper
        $mapper = $this->_createMapper($target['name']);

        if (!is_callable(array($mapper, $method))) {
            throw new sspmod_janus_REST_Exception_NotImplemented(
                sprintf('Unsupported request method \'%s\'', $method)
            );
        }

        if ($this->_request->isPut() && ($method !== 'GET')) {
            throw new sspmod_janus_REST_Exception_NotImplemented(
                'Use PUT on entity collection to create a new resource'
            );
        }

        // get(), post(), delete(), etc
        return $mapper->$method($target['id']);
    }

    /**
     * @param string $type
     * @return sspmod_janus_Mapper
     */
    protected function _createMapper($type)
    {
        if (!$this->isSupportedEntityType($type)) {
            throw new sspmod_janus_REST_Exception_NotFound(
                sprintf('Unknown entity type \'%s\'', $type)
            );
        }

        $casedType = '';
        foreach (explode('-', $type) as $part) {
            $casedType .= ucfirst($part);
        }

        $className = 'sspmod_janus_REST_Mapper_' . $casedType;
        if (!class_exists($className)) {
            throw new sspmod_janus_REST_Exception(
                'Bad configuration for entity type (missing mapper)'
            );
        }

        return new $className(
            $this->_request, $this->_response
        );
    }

    /**
     * @return array all available collections
     */
    protected function _getRootCollectionResources()
    {
        return array(
            '/idp', '/sp', '/arp', '/sp-metadata', 'idp-metadata'
        );
    }

    /**
     * Returns API config value
     *
     * @param string $name
     * @return mixed
     */
    protected function _getConfigValue($name)
    {
        return SimpleSAML_Configuration::getConfig('module_janus.php')
            ->getConfigItem('rest-api')
            ->getValue($name);
    }
}