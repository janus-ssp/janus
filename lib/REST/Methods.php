<?php
class sspmod_janus_REST_Methods
{
    public static function isProtected($method)
    {
        $protected_methods = array(
            'method_arp', 
            'method_getUser', 
            'method_getEntity', 
            'method_getMetadata', 
            'method_isConnectionAllowed',
            'method_getIdpList',
            'method_getSpList', 
            'method_findIdentifiersByMetadata');

        return in_array($method, $protected_methods);
    }
    
    public static function method_echo($data, &$status)
    {
        if(isset($data['string'])) {
            return $data['string'];
        } 

        return 'JANUS';
    }

    public static function method_arp($data, &$status)
    {
        if (!isset($data["entityid"])) {
            $status = 400;
            return '';
        }
        
        $revisionid = null;

        if(isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionid = $data['revision'];
        }
        
        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $econtroller->setEntity($data['entityid'], $revisionid);
    
        $arp = $econtroller->getArp();
        
        if ($arp==NULL) return NULL; // no arp set for this SP
        
        $result = array();
        $result["name"] = $arp->getName();
        $result["description"] = $arp->getDescription();         
        $result["attributes"] = $arp->getAttributes();
        
        return $result;
    }

    public static function method_getUser($data, &$status) {
        if (!isset($data["userid"])) {
            $status = 400;
            return '';
        }

        $config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $user = new sspmod_janus_User($config->getValue('store'));
        $user->setUserid($data['userid']);
        $user->load(sspmod_janus_User::USERID_LOAD);

        $result = array();

        $result['uid'] = $user->getUid();
        $result['userid'] = $user->getUserid();
        $result['active'] = $user->getActive();
        $result['type'] = $user->getType();
        $result['data'] = $user->getdata();

        return $result;
    }

    public static function method_getEntity($data, &$status) {
        if (!isset($data["entityid"])) {
            $status = 400;
            return '';
        }

        $revisionid = null;

        if(isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionid = $data['revision'];
        }

        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $entity = $econtroller->setEntity($data['entityid'], $revisionid);

        $result = array();

        $result['eid'] = $entity->getEid();
        $result['entityid'] = $entity->getEntityid();
        $result['revision'] = $entity->getRevisionid();
        $result['parent'] = $entity->getParent();
        $result['revisionnote'] = $entity->getRevisionnote();
        $result['type'] = $entity->gettype();
        $result['allowedall'] = $entity->getAllowedAll();
        $result['workflow'] = $entity->getWorkflow();
        $result['metadataurl'] = $entity->getMetadataURL();
        $result['prettyname'] = $entity->getPrettyname();
        $result['arp'] = $entity->getArp();
        $result['user'] = $entity->getUser();
        
        return $result;
    }

    public static function method_getMetadata($data, &$status)
    {
        if (!isset($data["entityid"])) {
            $status = 400;
            return '';
        }

        $revisionid = null;

        if(isset($data['revision']) && ctype_digit($data['revision'])) {
            $revisionid = $data['revision'];
        }
        
        $keys = array();
        if (isset($data["keys"])) {
            $keys = explode(",", $data["keys"]);
        }
        $result = self::_getMetadataForEntity($data["entityid"], $revisionid, $keys);

        return $result;
    }

    // Is an SP allowed to connect to a certain IDP? (checks the SP's white and blacklist). 
    public static function method_isConnectionAllowed($data, &$status)
    {
        if (!isset($data["spentityid"]) || !isset($data["idpentityid"])) {
            $status = 400;
            return '';
        }

        $sprevisionid = null;

        if(isset($data['sprevision']) && ctype_digit($data['sprevision'])) {
            $sprevisionid = $data['sprevision'];
        }

        $specontroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $specontroller->setEntity($data['spentityid'], $sprevisionid);

        if ($specontroller->getAllowedAll()!="yes") {
            
            $spbloked = $specontroller->getBlockedEntities();
            if(count($spbloked) && !array_key_exists($data['idpentityid'], $spbloked)) {
                return array(true);
            }         
            $spallowed = $specontroller->getAllowedEntities();
            if (count($spallowed) && array_key_exists($data['idpentityid'], $spallowed)) {
                return array(true);
            }
    
            return array(false);
    
        }
        return array(true);
    }

    public static function method_findIdentifiersByMetadata($data, &$status)
    {
        if (!isset($data["key"]) || !isset($data["value"]) || !isset($data['userid'])) {
            $status = 400;
            return '';
        }

        $ucontroller = new sspmod_janus_UserController(SimpleSAML_Configuration::getConfig('module_janus.php'));
    
        $ucontroller->setUser($data['userid']);

        $entities = $ucontroller->searchEntitiesByMetadata($data['key'], $data['value']);

        $result = array();
        
        foreach($entities AS $entity) {
            $result[] = $entity->getentityid();
        }

        return $result;
    }
    
/**
     * Unfinished implementation, awaits blacklist/whitelist implementation in janus.
     * For now, uses in efficient query that retrieves all eids (regardless of blacklist)
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.  
     *        spentityid (optional) - List only those idps which are 
     *                                whitelisted against the SP identified by
     *                                this parameter
     *                      
     */
    public static function method_getIdpList($data, &$status)
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
        
        return self::_getEntities("saml20-idp", $filter, $spEntityId);
    }
    
    /**
     * Retrieves a list of all Service Providers. 
     * @todo Use blacklist/whitelist
     * 
     *        The entries in $data for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.
     */
    public static function method_getSpList($data, &$status)
    {
        $filter = array();
        
        if (isset($data["keys"]) && $data["keys"]!="") {
            $filter = explode(",", $data["keys"]);
            
            // We also need the identifier
            if (!in_array("entityID", $filter)) {
                $filter[] = "entityID";
            }
        }
        
        return self::_getEntities("saml20-sp", $filter);
    }
    
    protected static function _getMetadataForEntity($entity, $revisionid = NULL, $keys=array())
    {
        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $entity = $econtroller->setEntity($entity, $revisionid);

        $metadata = $econtroller->getMetadata();

        $result = array();

        foreach($metadata AS $meta) {;
            if(count($keys) == 0 || in_array($meta->getKey(), $keys)) {
                $result[$meta->getKey()] = $meta->getValue();
            }
        }
        
        return $result;
        
    }
    
 /** 
     * Retrieve all entity metadata for all entities of a certain type.
     * @param String $type Supported types: "saml20-idp" or "saml20-sp"
     * @param Array $keys optional list of metadata keys to retrieve. Retrieves all if blank
     * @param String $allowedEntityId if passed, returns only those entities that are 
     *                         whitelisted against the given entity
     * @return Array Associative array of all metadata. The key of the array is the identifier
     */
    protected static function _getEntities($type, $keys=array(), $allowedEntityId=NULL)
    {
        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));
        
        $ucontroller = new sspmod_janus_UserController(SimpleSAML_Configuration::getConfig('module_janus.php'));   
        
        $entities = array();
        
        if (isset($allowedEntityId)) {
            $econtroller->setEntity($allowedEntityId);
            $econtroller->loadEntity();
            
            if ($econtroller->getEntity()->getAllowedAll()=="yes") {
                
                $entities = $ucontroller->searchEntitiesByType($type);
                                
            } else {
                $allowedEntities = $econtroller->getAllowedEntities();

                // Check the whitelist
                if (count($allowedEntities)) {
                    foreach($allowedEntities as $entityid=>$data) {
                        $entities[] = $data["remoteentityid"];
                   }
                } else {
                    // Check the blacklist
                    $blockedEntities = $econtroller->getBlockedEntities();
                    if (count($blockedEntities)) {
                        $blockedEntityIds = array();
                        foreach ($blockedEntities as $entityid=>$data) {
                            $blockedEntityIds[] = $data["remoteentityid"];
                        }
                  
                        $all = $ucontroller->searchEntitiesByType($type);
                        $list = array();
                        foreach($all as $entity) {
                            $list[] = $entity->getEntityId();
                        }
                        // Return all entities that are not in the blacklist
                        $entities = array_diff($list, $blockedEntityIds);
                    }
                    
                }
            }
            
        } else {
            $entities = $ucontroller->searchEntitiesByType($type);    
        }
        
        $result = array();
        
        
        foreach($entities as $entity) {
           $data = self::_getMetadataForEntity($entity, NULL, $keys);
           if (is_object($entity)) {
               $entityId = $entity->getEntityId();
           } else {
               $entityId = $entity;
           }
           $result[$entityId] = $data;          
      
        }
        return $result;
    }
}
