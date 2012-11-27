<?php

class sspmod_janus_REST_PermissionManager
{
    /**
     * @var sspmod_janus_REST_Request $_request
     */
    protected $_request = null;

    /**
     * @param sspmod_janus_REST_Request $request
     */
    public function __construct(sspmod_janus_REST_Request $request)
    {
        $this->_request = $request;
    }

    /**
     * @param sspmod_janus_User $user
     * @return bool true on success
     * @throws sspmod_janus_REST_Exception_NotAuthorized
     */
    public function authorize(sspmod_janus_User $user)
    {
        // get request entity for entity-level permissions
        $targetEntity = $this->_request->getTargetEntity();

        // see if anything is allowed based on user roles
        foreach ((array)$user->getType() as $type) {
            switch ($type) {
                case 'rest-admin':
                    return true;
                case 'rest-proxy':
                    if ($this->_request->isGet()) {
                        return true;
                    }
                    break;
                case 'rest-idp-admin':
                    if ($targetEntity['name'] === 'idp') {
                        return true;
                    }
                    break;
                case 'rest-sp-admin':
                    if ($targetEntity['name'] === 'sp') {
                        return true;
                    }
                    break;
                case 'rest-sp':
                    if (
                        ($targetEntity['name'] === 'sp') &&
                        ($this->_isAllowedForEntity($user, $targetEntity))
                    ) {
                        return true;
                    }
                    break;
                case 'rest-idp':
                    if (
                        ($targetEntity['name'] === 'idp') &&
                        ($this->_isAllowedForEntity($user, $targetEntity))
                    ) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Create user from authorization header
     *
     * @return \sspmod_janus_User
     * @throws sspmod_janus_REST_Exception_NotAuthorized
     */
    public function authenticate()
    {
        list($username, $password) = $this->_parseAuthorizationHeader();

        $user = new sspmod_janus_User(
            SimpleSAML_Configuration::getConfig('module_janus.php')->getValue('store')
        );
        $user->setUserid($username);

        $controller = sspmod_janus_REST_Mapper_Abstract::getUserController();
        $controller->setUser($user);

        // load user
        if(!$user->load(sspmod_janus_User::USERID_LOAD)) {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'User not found'
            );
        }

        // load allowed entities
        $controller->getEntities();

        // check active flag
        if ($user->getActive() !== 'yes') {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'User is not active'
            );
        }

        // check password
        $salt     = $this->_getConfigValue('authentication-salt');

        //! @todo implement hashing of password (janus gui)
//        $checkSum = sha256($salt . $password);
        $checkSum = $password;

        if ($user->getSecret() !== $checkSum) {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'User password mismatch'
            );
        }

        return $user;
    }

    /**
     * Returns true if the user has any of the provided roles
     *
     * @param sspmod_janus_User $user
     * @param array $roles
     * @return boolean
     */
    public function hasRole(sspmod_janus_User $user, $roles)
    {
        foreach ((array)$user->getType() as $role) {
            if (in_array($role, (array)$roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param sspmod_janus_User $user
     * @param string $name
     * @return bool
     * @throws sspmod_janus_REST_Exception_NotAuthorized
     */
    public function hasFieldPermissions(sspmod_janus_User $user, $name)
    {
        if ($this->hasRole($user, 'rest-admin')) {
            return true;
        }

        if ($name === 'arp') {
            return $this->hasRole($user, 'rest-arp');
        }

        if ($name === 'blocked') {
            return $this->hasRole($user, 'rest-acl');
        }

        if ($name === 'allowed') {
            return $this->hasRole($user, 'rest-acl');
        }

        if ($name === 'allowall') {
            return $this->hasRole($user, 'rest-acl');
        }

        return true;
    }

    /**
     * Create user from authorization header
     *
     * @return array (username, password)
     * @throws sspmod_janus_REST_Exception_NotAuthorized
     */
    protected function _parseAuthorizationHeader()
    {
        $header = $this->_request->getHeader('Authorization');
        if (!$header) {
            $this->_response->addHeader('WWW-Authenticate', sprintf(
                'Basic realm="%s"', $this->_getConfigValue('authentication-realm')
            ));

            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'Missing Authorization header'
            );
        }

        // parse header
        if (!preg_match('#Basic (.+)#', $header, $matches)) {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'Authorization header must match "Basic [base64 encoded username:password]"'
            );
        }

        $decoded  = explode(':', base64_decode($matches[1]));
        $password = array_pop($decoded);

        if (count($decoded) === 0) {
            throw new sspmod_janus_REST_Exception_NotAuthorized(
                'Error parsing Authorization header'
            );
        }

        return array(
            implode(':', $decoded),
            $password
        );
    }

    /**
     * @param \sspmod_janus_User $user
     * @param array $entity
     */
    protected function _isAllowedForEntity(sspmod_janus_User $user, array $target)
    {
        if ($this->hasRole($user, array('rest-admin', 'rest-sp-admin', 'rest-idp-admin'))) {
            return true;
        }

        // on collections, allow list only
        if (empty($target['id'])) {
            return $this->_request->isGet();
        }

        $controller = sspmod_janus_REST_Mapper_Abstract::getUserController();
        foreach ($controller->getEntities() as $entity) {
            if ($entity->getEid() === $target['id']) {
                return true;
            }
        }

        return false;
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