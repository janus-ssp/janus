<?php

abstract class sspmod_janus_REST_Mapper_Abstract
{
    /**
     * @var sspmod_janus_UserController
     */
    protected static $_userController = null;

    /**
     * @var sspmod_janus_EntityController
     */
    protected static $_entityController = null;

    /**
     * @var SimpleSAML_Configuration
     */
    protected static $_config = null;

    /**
     * @var sspmod_janus_REST_Request
     */
    protected $_request = null;

    /**
     * @var sspmod_janus_REST_Response
     */
    protected $_response = null;

    /**
     * @param sspmod_janus_REST_Request $request
     * @param sspmod_janus_REST_Response $response
     */
    public function __construct(sspmod_janus_REST_Request $request,
        sspmod_janus_REST_Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;
    }

    /**
     * @return array
     * @throws sspmod_janus_REST_Exception_NotImplemented
     */
    public function getCollection()
    {
        throw new sspmod_janus_REST_Exception_NotImplemented();
    }

    /**
     * @param string $id
     * @throws sspmod_janus_REST_Exception_NotImplemented
     */
    public function get($id)
    {
        throw new sspmod_janus_REST_Exception_NotImplemented();
    }

    /**
     * @throws sspmod_janus_REST_Exception_NotImplemented
     */
    public function put()
    {
        throw new sspmod_janus_REST_Exception_NotImplemented();
    }

    /**
     * @param string $id
     * @throws sspmod_janus_REST_Exception_NotImplemented
     */
    public function post($id)
    {
        throw new sspmod_janus_REST_Exception_NotImplemented();
    }

    /**
     * @param string $id
     * @throws sspmod_janus_REST_Exception_NotImplemented
     */
    public function delete($id)
    {
        throw new sspmod_janus_REST_Exception_NotImplemented();
    }

    /**
     * @return \SimpleSAML_Configuration
     */
    public static function getConfig()
    {
        if (self::$_config === null) {
            self::$_config = SimpleSAML_Configuration::getConfig('module_janus.php');
        }

        return self::$_config;
    }

    /**
     * @return \sspmod_janus_UserController
     */
    public static function getUserController()
    {
        //! @todo fixme, doesn't seem to work when configured in module_janus.php
        $orig = self::getConfig()->toArray();
        $orig['access']['allentities']['role'] = array(
            'rest-admin', 'rest-sp-admin', 'rest-idp-admin'
        );

        $config = new SimpleSAML_Configuration($orig, 'dummy');

        if (self::$_userController === null) {
            self::$_userController = new sspmod_janus_UserController($config);
        }

        return self::$_userController;
    }

    /**
     * @return \sspmod_janus_EntityController
     */
    public static function getEntityController()
    {
        if (self::$_entityController === null) {
            return self::resetEntityController();
        }

        return self::$_entityController;
    }

    /**
     * @param sspmod_janus_REST_Request $request
     * @return sspmod_janus_REST_PermissionManager
     */
    public static function getPermissionManager(sspmod_janus_REST_Request $request)
    {
        return new sspmod_janus_REST_PermissionManager($request);
    }

    /**
     * @return \sspmod_janus_EntityController
     */
    public static function resetEntityController()
    {
        self::$_entityController = new sspmod_janus_EntityController(
            self::getConfig()
        );

        return self::$_entityController;
    }

    /**
     * Set id on janus Entity object, performs duck-typing on the
     * id value:
     *  - is_numeric: set eid
     *  - else: set entityid
     *
     * @param sspmod_janus_Entity $entity
     * @param int|string $id
     * @param string $revision
     * @return bool result
     */
    protected function _setEntityId(sspmod_janus_Entity $entity, $id, $revision = null)
    {
        if ($revision !== null) {
            $entity->setRevisionid($revision);
        }

        if (is_numeric($id)) {
            $entity->setEid($id);
            if (!$entity->load()) {
                //! @todo implement more accurate error handling in janus libs
                throw new sspmod_janus_REST_Exception_NotFound(
                    sprintf('Entity with ID \'%s\' not found', $id), null, $e
                );
            }

            self::getEntityController()
                ->setEntity($entity);

            return true;
        } else {
            // not supported for two reasons:
            //  1. entityid makes a very clumsy URL (https%253A%252F%252Fopenidp.feide.no%252F)
            //  2. less REST-full having two handles for the same object
            throw new sspmod_janus_REST_Exception_BadRequest(
                'Resource can not contain entityid, use eid or a collection filter'
            );
        }

        return true;
    }
}
