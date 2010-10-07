<?php
class sspmod_janus_REST_Methods
{
    public static function isProtected($method)
    {
        $protected_methods = array('method_arp');

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
        
        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $econtroller->setEntity($data['entityid']);
    
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

        $econtroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $entity = $econtroller->setEntity($data['entityid'], $revisionid);

        $metadata = $econtroller->getMetadata();

        $keys = array();
        if(isset($data['keys'])) {
            $keys = explode(',', $data['keys']);
        }

        $result = array();

        foreach($metadata AS $meta) {;
            if(count($keys) == 0 || in_array($meta->getKey(), $keys)) {
                $result[$meta->getKey()] = $meta->getValue();
            }
        }

        return $result;
    }

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

        $spbloked = $specontroller->getBlockedEntities();
            
        if(array_key_exists($data['idpentityid'], $spbloked)) {
            return array(false);
        }

        $idprevisionid = null;

        if(isset($data['idprevision']) && ctype_digit($data['idprevision'])) {
            $idprevisionid = $data['idprevision'];
        }

        $idpecontroller = new sspmod_janus_EntityController(SimpleSAML_Configuration::getConfig('module_janus.php'));

        $idpentity = $idpecontroller->setEntity($data['idpentityid'], $idprevisionid);
        
        $idpbloked = $idpecontroller->getBlockedEntities();
        
        if(array_key_exists($data['spentityid'], $idpbloked)) {
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
}
