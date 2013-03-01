<?php
class sspmod_janus_REST_Methods
{
    /**
     * Blacklist of methods that are protected (and need authentication to use).
     */
    public static function isProtected($method)
    {
        $protected_methods = array(
            'method_arp', 
            'method_getUser', 
            'method_getEntity', 
            'method_getMetadata',
            'method_isConnectionAllowed',
            'method_getAllowedIdps',
            'method_getAllowedSps',
            'method_getIdpList',
            'method_getSpList', 
            'method_findIdentifiersByMetadata'
        );

        return in_array($method, $protected_methods);
    }

    /**
     * Echo back input received, for uptime testing.
     *
     * @access public (see isProtected)
     * @static
     * @param array $data Request parameters for echo method, supports:
     *                    - string $data['string']: string to echo back
     * @return string
     */
    public static function method_echo($data)
    {
        if(isset($data['string'])) {
            return $data['string'];
        } 

        return 'JANUS';
    }

    /**
     * Get the Attribute Release Policy for a given Entity
     *
     * @access protected (see isProtected public method)
     * @static
     * @param array $data Request parameters for arp method, supports:
     *                      - string $data['entityid']: Entity ID to get ARP for
     *                      - int    $data['revision']: Revision of Entity to get ARP for (if not set, last revision is used)
     * @param int   $statusCode HTTP status code to return
     * @return array|stdClass|string Attribute release policy data
     */
    public static function method_arp($data, &$statusCode)
    {
        if (!isset($data['entityid'])) {
            $statusCode = 400;
            return '';
        }
        
        $revisionId = null;
        if (isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionId = $data['revision'];
        }
        
        $entityController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $entityController->setEntity($data['entityid'], $revisionId);
    
        $arp = $entityController->getArp();
        if (!$arp) {
            // no arp set for this SP
            return new stdClass();
        }
        
        $result = array();
        $result['name']         = $arp->getName();
        $result['description']  = $arp->getDescription();
        $result['attributes']   = $arp->getAttributes();
        
        return $result;
    }

    /**
     * Get User information
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for getUser method, supports:
     *                      - string $data['userid']: UserID (login name) to get data for
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string User information
     */
    public static function method_getUser($data, &$statusCode)
    {
        if (!isset($data["userid"])) {
            $statusCode = 400;
            return '';
        }

        $config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $user = new sspmod_janus_User($config->getValue('store'));
        $user->setUserid($data['userid']);
        $user->load(sspmod_janus_User::USERID_LOAD);

        $result = array();
        $result['uid']      = $user->getUid();
        $result['userid']   = $user->getUserid();
        $result['active']   = $user->getActive();
        $result['type']     = $user->getType();
        $result['data']     = $user->getdata();

        return $result;
    }

    /**
     * Get Entity Information.
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for getEntity method, supports:
     *                      - string $data['entityid']: Entity ID to get information
     *                      - int    $data['revision']: Revision of Entity to get information for (if not set, last revision is used)
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string Entity information
     */
    public static function method_getEntity($data, &$statusCode) {
        if (!isset($data["entityid"])) {
            $statusCode = 400;
            return '';
        }

        $revisionId = null;
        if (isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionId = $data['revision'];
        }

        $entityController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $entity = $entityController->setEntity($data['entityid'], $revisionId);

        $result = array();
        $result['eid']          = $entity->getEid();
        $result['entityid']     = $entity->getEntityid();
        $result['revision']     = $entity->getRevisionid();
        $result['parent']       = $entity->getParent();
        $result['revisionnote'] = $entity->getRevisionnote();
        $result['type']         = $entity->gettype();
        $result['allowedall']   = $entity->getAllowedAll();
        $result['workflow']     = $entity->getWorkflow();
        $result['metadataurl']  = $entity->getMetadataURL();
        $result['prettyname']   = $entity->getPrettyname();
        $result['arp']          = $entity->getArp();
        $result['manipulation'] = $entity->getManipulation();
        $result['user']         = $entity->getUser();

        return $result;
    }

    /**
     * Get Entity metadata entries, note that this does not include the information about the entity that JANUS has.
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for getMetadata method, supports:
     *                      - string $data['entityid']: Entity ID to get information
     *                      - int    $data['revision']: Revision of Entity to get information for (if not set, last revision is used)
     *                      - string $data['keys']: Output keys
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string Entity metadata
     */
    public static function method_getMetadata($data, &$statusCode)
    {
        if (!isset($data["entityid"])) {
            $statusCode = 400;
            return '';
        }

        $revisionId = null;
        if (isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionId = $data['revision'];
        }
        
        $keys = array();
        if (isset($data["keys"])) {
            $keys = explode(",", $data["keys"]);
        }

        $result = self::_getMetadataForEntity($data["entityid"], $revisionId, $keys);
        if (!$result) {
            $statusCode = 404;
        }

        return $result;
    }

    /**
     * Is an SP allowed to connect to a certain IDP? (checks the SP's & IDP's white and blacklist).
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for getMetadata method, supports:
     *                      - string $data['spentityid']: Service Provider Entity ID to check
     *                      - int    $data['sprevision']: Revision of Service Provider to get information for (if not set, last revision is used)
     *                      - string $data['idpentityid']: Identity Provider Entity ID to check
     *                      - int    $data['idprevision']: Revision of Identity Provider to get information for (if not set, last revision is used)
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string Whether the connection is allowed
     */
    public static function method_isConnectionAllowed($data, &$statusCode)
    {
        if (!isset($data["spentityid"]) || !isset($data["idpentityid"])) {
            $statusCode = 400;
            return '';
        }

        $spRevision = null;
        if(isset($data['sprevision']) && ctype_digit($data['sprevision'])) {
            $spRevision = $data['sprevision'];
        }

        // @todo see if controllers can be instantiated only once
        $spController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $spController->setEntity($data['spentityid'], $spRevision);

        $idpRevision = null;
        if(isset($data['idprevision']) && ctype_digit($data['idprevision'])) {
            $idpRevision = $data['idprevision'];
        }

        $idpController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $idpController->setEntity($data['idpentityid'], $idpRevision);

        // Check the SP metadata whether the SP-IdP connection is allowed.
        $isSpAllowed = self::_checkSPMetadataIsConnectionAllowed(
            $spController->getEntity(),
            $idpController->getEntity()
        );

        // Check the IdP metadata whether the SP-IdP connection is allowed.
        $isIdpAllowed = self::_checkIdpMetadataIsConnectionAllowed(
            $spController->getEntity(),
            $idpController->getEntity()
        );

        return ($isSpAllowed && $isIdpAllowed) ? array(true) : array(false);
    }

    /**
     * Get only the Identity Providers that a Service Provider is allowed to connect to.
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for getMetadata method, supports:
     *                      - string $data['spentityid']: Service Provider Entity ID to check
     *                      - int    $data['sprevision']: Revision of Service Provider to get information for (if not set, last revision is used)
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string Allowed Identity Providers
     */
    public static function method_getAllowedIdps($data, &$statusCode)
    {
        $config = SimpleSAML_Configuration::getConfig('module_janus.php');

        if (!isset($data['spentityid'])) {
            $statusCode = 400;
            return '';
        }

        $spRevision = null;
        if (isset($data['sprevision']) && ctype_digit($data['sprevision'])) {
            $spRevision = $data['sprevision'];
        }

        $spController = new sspmod_janus_EntityController($config);
        $spController->setEntity($data['spentityid'], $spRevision);
        $sp = $spController->getEntity();

        if (!$sp instanceof sspmod_janus_Entity) {
            $statusCode = 400;
            return '';
        }

        $allowedIdps = array();
        if ($spController->getAllowedAll() === "yes") {
            $userController   = new sspmod_janus_UserController($config);
            $allowedIdps = $userController->searchEntitiesByType('saml20-idp');
        }
        else {
            $allowedIdps = $spController->getAllowedEntities();
            $blockedIdps = $spController->getBlockedEntities();

            if (count($blockedIdps)) {
                $allowedIdps = array_diff($allowedIdps, $blockedIdps);
            }
        }

        $idpController = new sspmod_janus_EntityController($config);
        $results = array();
        foreach ($allowedIdps as $idpData) {
            $idpController->setEntity($idpData['remoteeid'], $idpData['remoterevisonid']);
            $idp = $idpController->getEntity();
            if (self::_checkIdPMetadataIsConnectionAllowed($sp, $idp)
            ) {
                $results[] = $idp->getEntityid();
            }
        }
        return $results;
    }

    /**
     * Get the Service Providers that a given Identiy Provder is allowed to connect to
     *
     * @access protected (see isProtected)
     * @static
     * @param array $data Request parameters for method, supports:
     *                      - string $data['idpentityid']: Identity Provider Entity ID to check
     *                      - int    $data['idprevision']: Revision of Identity Provider to get information for (if not set, last revision is used)
     * @param int $statusCode HTTP Status code to use in response
     * @return array|string Service Providers the Identity Provider is allowed to connect to
     */
    public static function method_getAllowedSps($data, &$statusCode)
    {
        $config = SimpleSAML_Configuration::getConfig('module_janus.php');

        if (!isset($data['idpentityid'])) {
            $statusCode = 400;
            return '';
        }

        $idpRevision = null;
        if (isset($data['idprevision']) && ctype_digit($data['idprevision'])) {
            $idpRevision = $data['idprevision'];
        }

        $idpController = new sspmod_janus_EntityController($config);
        $idpController->setEntity($data['idpentityid'], $idpRevision);
        $idp = $idpController->getEntity();

        if (!$idp instanceof sspmod_janus_Entity) {
            $statusCode = 400;
            return '';
        }

        $idp = $idpController->getEntity();

        $allowedSps = array();
        if ($idpController->getAllowedAll() === "yes") {
            $userController   = new sspmod_janus_UserController($config);
            $allowedSps = $userController->searchEntitiesByType('saml20-sp');
        }
        else {
            $allowedSps = $idpController->getAllowedEntities();
            $blockedSps = $idpController->getBlockedEntities();

            if (count($blockedSps)) {
                $allowedSps = array_diff($allowedSps, $blockedSps);
            }
        }

        $spController = new sspmod_janus_EntityController($config);
        $results = array();
        /** @var $sp sspmod_janus_Entity */
        foreach ($allowedSps as $spData) {
            $idpController->setEntity($spData['remoteeid'], $spData['remoterevisionid']);
            $sp = $spController->getEntity();
            if (self::_checkSPMetadataIsConnectionAllowed($sp, $idp)) {
                $results[] = $sp->getEntityid();
            }
        }
        return $results;
    }

    public static function method_findIdentifiersByMetadata($data, &$statusCode)
    {
        if (!isset($data["key"]) || !isset($data["value"]) || !isset($data['userid'])) {
            $statusCode = 400;
            return '';
        }

        $userController = new sspmod_janus_UserController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $userController->setUser($data['userid']);
        $entities = $userController->searchEntitiesByMetadata($data['key'], $data['value']);

        $result = array();
        /** @var $entity sspmod_janus_Entity */
        foreach($entities AS $entity) {
            $result[] = $entity->getEntityId();
        }

        return $result;
    }
    
    /**
     * Get Identity Providers
     * @param array $data The request parameters (typically from $_REQUEST)
     *        The entries in $data for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.  
     *        spentityid (optional) - List only those idps which are 
     *                                whitelisted against the SP identified by
     *                                this parameter
     * @return array Identity Provider as assoc array with entityId as key
     */
    public static function method_getIdpList($data)
    {
        $filter = array();
        
        // here we have access to $this->_entityController->getBlockedEntities() 
        // but we need a whitelist approach.
        if (isset($data["keys"]) && $data["keys"]!="") {
            $filter = explode(",", $data["keys"]);            
        }
        
        $spEntityId = NULL;
        if (isset($data["spentityid"]) && $data["spentityid"]!="") { 
            $spEntityId = $data["spentityid"];
        }

        return self::_getFormattedEntitiesForType("saml20-idp", $filter, $spEntityId);
    }
    
    /**
     * Get Service Providers
     * @param array $data The request parameters (typically from $_REQUEST)
     *        The entries in $data for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.
     * @return array Service Provider as assoc array with entityId as key
     */
    public static function method_getSpList($data)
    {
        $filter = array();
        
        if (isset($data["keys"]) && $data["keys"]!="") {
            $filter = explode(",", $data["keys"]);
            
            // We also need the identifier
            if (!in_array("entityID", $filter)) {
                $filter[] = "entityID";
            }
        }
        
        return self::_getFormattedEntitiesForType("saml20-sp", $filter);
    }

    /**
     * Get requested metadata for an entity.
     *
     * Note that we support eid, entityid or sspmod_janus_Entity as input for an entity)
     * but we make sure it's always a sspmod_janus_Entity object after we're done.
     *
     * @static
     * @param mixed $entity
     * @param null  $revisionId
     * @param array $keys
     * @return array|bool
     */
    protected static function _getMetadataForEntity(sspmod_janus_Entity &$entity, $revisionId = NULL, $keys=array())
    {
        $cacheStore = SimpleSAML_Store::getInstance();

        // Only cache when memcache is configured, for caching in session does not work with REST
        // and caching database results in a database is pointless
        $useCache = false;
        if($cacheStore instanceof SimpleSAML_Store_Memcache) {
            $useCache = true;
        }

        if ($useCache) {
            // Make sure revision id is always set so it the cache key will be correct
            if (is_null($revisionId)) {
                $revisionId = $entity->getRevisionid();
            }

            // Try to get result from cache
            $cacheKey = 'entity-metadata-' . $entity->getEid() . '-' . $revisionId;
            $result = $cacheStore->get('array', $cacheKey);
            if (!is_null($result)) {
                return $result;
            }
        }

        $entityController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        /** @var $entity sspmod_janus_Entity */
        $entity = $entityController->setEntity($entity, $revisionId);
        if (!$entity->getWorkflow()) {
            return false;
        }

        $metadata = $entityController->getMetadata();

        $result = array();
        foreach($metadata AS $meta) {;
            if (count($keys) == 0 || in_array($meta->getKey(), $keys)) {
                $result[$meta->getKey()] = $meta->getValue();
            }
        }

        // Add disable consent
        foreach(array_keys($entityController->getDisableConsent()) as $entityIndex => $entityUrl) {
            $result['disableConsent:' . $entityIndex] = $entityUrl;
        }

        if ($useCache) {
            // Store metadata in cache, note that this does not have to be flushed since a new revision
            // will trigger a new version of the cache anyway
            $cacheStore->set('array', $cacheKey, $result);
        }

        return $result;
    }
    
    /**
     * Retrieve all entity metadata for all entities of a certain type.
     *
     * @param String $type            Supported types: "saml20-idp" or "saml20-sp"
     * @param Array  $keys            optional list of metadata keys to retrieve. Retrieves all if blank
     * @param String $allowedEntityId if passed, returns only those entities that are 
     *                                 whitelisted against the given entity
     * @return Array Metadata as EntityId -=> array( // data about entity );
     */
    protected static function _getFormattedEntitiesForType($type, $keys=array(), $allowedEntityId=NULL)
    {
        $entities = self::_getEntitiesForType($type, $allowedEntityId);
        
        $result = array();
        foreach($entities as $entity) {
           $data = self::_getMetadataForEntity($entity, NULL, $keys);
            /** @var $entity sspmod_janus_Entity */

           // Add workflow state info for optional filtering at client side
           $data['workflowState'] = $entity->getWorkflow();

           /** @var $entityId string */
           $entityId = $entity->getEntityId();
           $result[$entityId] = $data;
        }
        return $result;
    }

    /**
     * Get all entities for a given type (like saml20-idp or saml20-sp) and optional entity to check for access.
     *
     * @static
     * @param string      $type        Type of entities to get
     * @param null|string $forEntityId Entity (of opposite typ!) to check against.
     * @return array Entities
     */
    protected static function _getEntitiesForType($type, $forEntityId = null)
    {
        $entityController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $userController   = new sspmod_janus_UserController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        if (!$forEntityId) {
            return $userController->searchEntitiesByType($type);
        }

        $entityController->setEntity($forEntityId);
        $entityController->loadEntity();

        if ($entityController->getEntity()->getAllowedAll()=='yes') {
            return $userController->searchEntitiesByType($type);
        }

        $allowedEntities = $entityController->getAllowedEntities();
        if (count($allowedEntities) > 0) {
            return array_keys($allowedEntities);
        }

        // Check the blacklist
        $blockedEntities = $entityController->getBlockedEntities();
        if (count($blockedEntities) > 0) {
            $allEntities = $userController->searchEntitiesByType($type);
            $allEids = array();
            /** @var $entity sspmod_janus_Entity */
            foreach($allEntities as $entity) {
                $allEids[] = $entity->getEid();
            }
            // Return all entities that are not in the blacklist
            return array_diff($allEids, array_keys($blockedEntities));
        }

        // No entities allowed for this entity
        return array();
    }

    /**
     * Does the Service Provider allow a particular connection?
     *
     * @static
     * @param sspmod_janus_Entity       $spEid        Service Provider to check against
     * @param sspmod_janus_Entity       $idpEid       Identity Provider to check for
     * @return bool Is the connection allowed?
     */
    protected static function _checkSPMetadataIsConnectionAllowed(sspmod_janus_Entity $sp, sspmod_janus_Entity $idp)
    {
        $spController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $spController->setEntity($sp);

        $idpEid = $idp->getEid();

        if ($spController->getAllowedAll() === "yes") {
            return true;
        }

        $allowedIdps = $spController->getAllowedEntities();
        if (count($allowedIdps) && array_key_exists($idpEid, $allowedIdps)) {
            return true;
        }

        $blockedIdps = $spController->getBlockedEntities();
        if (count($blockedIdps) && !array_key_exists($idpEid, $blockedIdps)) {
           return true;
        }

        return false;
    }

    /**
     * Does the Identity Provider allow a particular connection?
     *
     * @static
     * @param string      sspmod_janus_Entity $sp         Service Provider to check for
     * @param string      sspmod_janus_Entity $idp        Identity Provider to check against
     * @return bool Is the connection allowed?
     */
    protected static function _checkIdPMetadataIsConnectionAllowed(sspmod_janus_Entity $sp, sspmod_janus_Entity $idp)
    {
        $idpController = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        $idpController->setEntity($idp);

        $spEid = $sp->getEid();

        if ($idpController->getAllowedAll() === "yes") {
            return true;
        }

        $allowedSps = $idpController->getAllowedEntities();
        if (count($allowedSps) > 0 && array_key_exists($spEid, $allowedSps)) {
            return true;
        }

        $blockedSps = $idpController->getBlockedEntities();
        if (count($blockedSps) > 0 && !array_key_exists($spEid, $blockedSps)) {
            return true;
        }

        return false;
    }
}
