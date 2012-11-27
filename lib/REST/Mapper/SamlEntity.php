<?php

abstract class sspmod_janus_REST_Mapper_SamlEntity extends sspmod_janus_REST_Mapper_Abstract
{
    /**
     * @var string idp/sp saml20 type
     */
    protected $_samlType = 'unknown';

    /**
     * @return array
     */
    public function getCollection()
    {
        $entities = self::getUserController()->getEntities();

        $collection = array();

        foreach ($entities as $entity) {
            if ($entity->getType() === $this->_samlType) {
                $collection[] = $this->_fromEntity($entity);
            }
        }

        return $collection;
    }

    /**
     * @param string $id
     * @return array
     */
    public function get($id)
    {
        $entity = new sspmod_janus_Entity(
            self::getConfig()
        );

        try {
            $this->_setEntityId($entity, (string)$id, $this->_request->getParameter('revision'));
        } catch (Exception $e) {
            // workaround for bad janus' error handling
            if (!preg_match('/could not get newest revision/i', $e->getMessage())) {
                throw $e;
            }

            throw new sspmod_janus_REST_Exception_NotFound(
                sprintf('Entity with ID \'%s\' not found', $id)
            );
        }

        // do not return an sp in /idp and vice versa
        if ($entity->getType() !== $this->_samlType) {
            throw new sspmod_janus_REST_Exception_NotFound(
                sprintf('Entity with ID \'%s\' is not of type \'%s\'', $id, $this->_samlType)
            );
        }

        return $this->_fromEntity($entity);
    }

    /**
     *
     * @param string $id
     * @return array
     */
    public function post($id)
    {
        $params = $this->_request->getParameters();

        $params['type'] = $this->_samlType;

        unset($params['eid']);
        unset($params['revisionid']);

        // creates entity object
        $entity = $this->_toEntity((string)$id, $params);

        $controller = self::getEntityController();
        $controller->loadEntity();

        // set prod/test workflow
        if (isset($params['workflow'])) {
            $this->_setWorkflowState($entity, $params['workflow']);
        }

        // update metadata, arp, blocked, allowed
        $this->_updateRelations();

        return $this->get($id);
    }

    /**
     *
     * @return array
     */
    public function put()
    {
        $params = $this->_request->getParameters();

        unset($params['type']);
        unset($params['eid']);
        unset($params['revisionid']);

        // use metadataurl as default entityid
        if (empty($params['entityid']) && !empty($params['metadataurl'])) {
            $params['entityid'] = $params['metadataurl'];
        }

        // check minimum values
        if (empty($params['entityid'])) {
            throw new sspmod_janus_REST_Exception_BadRequest(
                "Missing required entityid, provide at least 'metadataurl' or 'entityid' in the request body"
            );
        }

        // check entityid
        $controller = self::getUserController();
        if ($controller->isEntityIdInUse($params['entityid'], $message)) {
            // intentionally discarding $message
            throw new sspmod_janus_REST_Exception_BadRequest(
                sprintf('An entity with entityid \'%s\' already exists', $params['entityid'])
            );
        }

        $params['type'] = $this->_samlType;

        $controller = self::getEntityController();
        $entity = $this->_toEntity(null, $params);

        //! @todo improve error reporting
        $controller->loadEntity();

        // set prod/test workflow
        if (isset($params['workflow'])) {
            $this->_setWorkflowState($entity, $params['workflow']);
        } else {
            $defaultState = self::getConfig()->getValue('workflowstate.default');

            $this->_setWorkflowState($entity, $defaultState);
        }

        try {
            // update metadata, arp, blocked, allowed
            $this->_updateRelations();
        } catch (Exception $e) {
            $this->_getAdminUtil()->deleteEntity($entity->getEid());

            throw $e;
        }

        return $this->get($entity->getEid());
    }

    /**
     * @param string $id
     * @param array $parent
     * @throws sspmod_janus_REST_Exception_InternalServerError
     */
    public function delete($id, array $parent = null)
    {
        $entity = $this->get($id);

        //! @todo implement error handling
        $this->_getAdminUtil()->deleteEntity($entity['eid']);
    }

    /**
     * @param sspmod_janus_Entity $entity
     * @param string $newState
     * @return sspmod_janus_REST_Mapper_SamlEntity
     */
    protected function _setWorkflowState(sspmod_janus_Entity $entity, $newState)
    {
        if (
            ($entity->getWorkflow() === 'prodaccepted') ||
            ($newState === 'prodaccepted')
        ) {
            $user    = self::getUserController()->getUser();
            $manager = self::getPermissionManager($this->_request);

            if (!$manager->hasRole($user, array('rest-admin', 'rest-prod'))) {
                throw new sspmod_janus_REST_Exception_NotAuthorized(
                    'You are not authorized for production settings'
                );
            }
        }

        $entity->setWorkflow($newState);
    }

    /**
     * @param sspmod_janus_Entity $entity
     * @return array
     */
    protected function _fromEntity(sspmod_janus_Entity $entity)
    {
        $result = array();

        foreach ($this->_getMapping() as $entityKey => $apiKey) {
            $method = 'get' . ucfirst($entityKey);

            // hard-code exceptions
            if ($method === 'getType') {
                $method = 'gettype';
            }

            if (!is_callable(array($entity, $method))) {
                throw new sspmod_janus_REST_Exception_InternalServerError(
                    "Unknown key \'$entityKey\' in mapping of SAMLEntity"
                );
            }

            $result[$apiKey] = $entity->{$method}();
        }

        $controller = self::getEntityController();
        $controller->setEntity($entity);

        // add metadata
        $result['metadata'] = array();
        foreach ($controller->getMetadata() as $md) {
            $result['metadata'][$md->getKey()] = $md->getValue();
        }

        // get ARP
        if ($this->_samlType === 'saml20-idp') {
            $result['disableconsent'] = $this->_getLinkedEntities('disableConsent');
        } else {
            $arp = $controller->getArp();
            if ($arp instanceof sspmod_janus_ARP) {
                // add arp entries
                $result['arp'] = $arp->getAid();
            } else {
                $result['arp'] = null;
            }
        }

        $result['allowed'] = $this->_getLinkedEntities('allowed');
        $result['blocked'] = $this->_getLinkedEntities('blocked');
        $result['workflow'] = $entity->getWorkflow();

        return $result;
    }

    /**
     * @param string $id
     * @param array $data
     * @param int $revision
     * @return sspmod_janus_Entity $entity
     */
    protected function _toEntity($id, array $data, $revision = null)
    {
        $createNew = $this->_request->isPut();

        $entity = new sspmod_janus_Entity(
            self::getConfig(), $createNew
        );

        if (!$createNew) {
            $this->_setEntityId($entity, $id, $revision);
        } else {
            self::getEntityController()->setEntity($entity);
        }

        // check permissions
        $manager = self::getPermissionManager($this->_request);
        $user    = self::getUserController()->getUser();

        foreach ($data as $field => $value) {
            if (!$manager->hasFieldPermissions($user, $field)) {
                throw new sspmod_janus_REST_Exception_NotAuthorized(sprintf(
                    'Specific rights must be granted for field \'%s\'', $field
                ));
            }
        }

        // apply mapping
        foreach ($this->_getMapping() as $entityKey => $apiKey) {
            if (!isset($data[$apiKey])) {
                continue;
            }

            $method = 'set' . ucfirst($entityKey);

            // hard-code exceptions
            if ($method === 'setType') {
                $method = 'settype';
            }

            if (!is_callable(array($entity, $method))) {
                throw new sspmod_janus_REST_Exception_InternalServerError(
                    "Unknown key \'$entityKey\' in mapping of SAMLEntity"
                );
            }

            $entity->{$method}(
                $data[$apiKey]
            );
        }

        return $entity;
    }

    /**
     * Get blocked/allowed entities list
     *
     * @param string $type 'allowed' or 'blocked'
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _getLinkedEntities($type)
    {
        // get entities
        if ($type === 'blocked') {
            $entities = self::getEntityController()->getBlockedEntities();
        } else if ($type === 'allowed') {
            $entities = self::getEntityController()->getAllowedEntities();
        } else {
            $entities = self::getEntityController()->getDisableConsent();
        }

        $result = array();

        foreach ($entities as $entity) {
            if (isset($entity['remoteeid'])) {
                $result[] = $entity['remoteeid'];
            } else {
                $result[] = $entity['remoteentityid'];
            }
        }

        return $result;
    }

    /**
     * Updates metadata, arp, blocked, allowed from request
     * to database
     *
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _updateRelations()
    {
        $controller = self::getEntityController();

        $this->_addEntityMetadata($controller)
             ->_addEntityBlocked($controller)
             ->_addEntityAllowed($controller);

        if ($this->_samlType === 'saml20-idp') {
             $this->_addEntityDisableConsent($controller);
        } else {
             $this->_addEntityArp($controller);
        }

        $controller->saveEntity();
    }

    /**
     * Save entity metadata
     *
     * @param sspmod_janus_EntityController $controller
     * @return \sspmod_janus_REST_Mapper_SamlEntity
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _addEntityMetadata(sspmod_janus_EntityController $controller) {
        $user     = self::getUserController()->getUser();
        $manager  = self::getPermissionManager($this->_request);
        $metadata = $this->_request->getParameter('metadata', array());
        $filters  = self::getConfig()->getConfigItem('rest-api')->getArray('metadata-filter-roles');

        foreach ($metadata as $name => $value) {
            foreach ($filters as $filterName => $filter) {
                if (!preg_match($filter['pattern'], $name)) {
                    continue; // not filtered
                }

                if ($manager->hasRole($user, $filter['roles'])) {
                    continue;
                }

                throw new sspmod_janus_REST_Exception_NotAuthorized(sprintf(
                    'Not authorized for write access to metadata field \'%s\' by filter \'%s\'',
                    $name, $filterName
                ));
            }

            if ($value === null) {
                // delete metadata
                $controller->removeMetadata($name);

                continue;
            }

            foreach ($controller->getMetadata() as $md) {
                if (($md->getKey() === $name) && ($md->getValue() === $value)) {
                    continue; // value already there, all OK
                }
            }

            if (!$controller->addMetadata($name, $value)) {
                // ignore errors for POST (updating twice should be OK)
                if (!$controller->updateMetadata($name, $value) && $this->_request->isPut()) {
                    throw new sspmod_janus_REST_Exception_BadRequest(
                        "Invalid value or unknown metadata field '{$name}'"
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Add entity arp entry
     *
     * @param sspmod_janus_EntityController $controller
     * @return \sspmod_janus_REST_Mapper_SamlEntity
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _addEntityArp(sspmod_janus_EntityController $controller) {
        if ($this->_request->hasParameter('arp')) {
            $arp     = $this->_request->getParameter('arp');
            $user    = self::getUserController()->getUser();
            $manager = self::getPermissionManager($this->_request);

            if (!$manager->hasRole($user, array('rest-arp', 'rest-admin'))) {
                throw new sspmod_janus_REST_Exception_NotAuthorized(
                    'Not authorized for ARP updates'
                );
            }

            $controller->setArp(($arp) ? $arp : '0');
        }

        return $this;
    }

    /**
     * Add blocked entities
     *
     * @param sspmod_janus_EntityController $controller
     * @return \sspmod_janus_REST_Mapper_SamlEntity
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _addEntityAllowed(sspmod_janus_EntityController $controller) {
        if ($this->_request->hasParameter('allowed')) {
            $allowed = (array)$this->_request->getParameter('allowed');
            if ($allowed === null || $allowed === array()) {
                $controller->clearAllowedEntities();
            } else {
                foreach ($allowed as $allowEid) {
                    $controller->addAllowedEntity((string)$allowEid);
                }
            }
        }

        return $this;
    }

    /**
     * Add allowed entities
     *
     * @param sspmod_janus_EntityController $controller
     * @return \sspmod_janus_REST_Mapper_SamlEntity
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _addEntityBlocked(sspmod_janus_EntityController $controller) {
        if ($this->_request->hasParameter('blocked')) {
            $blocked = (array)$this->_request->getParameter('blocked');
            if ($blocked === null || $blocked === array()) {
                $controller->clearBlockedEntities();
            } else {
                foreach ($blocked as $blockEid) {
                    $controller->addBlockedEntity((string)$blockEid);
                }
            }
        }

        return $this;
    }


    /**
     * Add disable consent entities
     *
     * @param sspmod_janus_EntityController $controller
     * @return \sspmod_janus_REST_Mapper_SamlEntity
     * @throws sspmod_janus_REST_Exception_BadRequest
     */
    protected function _addEntityDisableConsent(sspmod_janus_EntityController $controller) {
        if ($this->_request->hasParameter('disableconsent')) {
            $requested = (array)$this->_request->getParameter('disableconsent');
            if ($requested === null || $requested === array()) {
                $controller->clearConsent();
            } else {
                foreach ($requested as $eid) {
                    $controller->addDisableConsent((string)$eid);
                }
            }
        }

        return $this;
    }

    /**
     * @return array mapping janus entity key => API key
     */
    protected function _getMapping()
    {
        return array(
            'eid'          => 'eid',
            'entityid'     => 'entityid',
            'parent'       => 'parent',
            'revisionid'   => 'revisionid',
            'revisionnote' => 'revisionnote',
            'type'         => 'type',
            'allowedAll'   => 'allowedall',
            'metadataURL'  => 'metadataurl',
            'arp'          => 'arp',
            'prettyname'   => 'prettyname',
            'manipulation' => 'manipulation',
            'user'         => 'user',
        );
    }

    /**
     * @return sspmod_janus_AdminUtil
     */
    protected function _getAdminUtil()
    {
        return new sspmod_janus_AdminUtil();
    }
}
