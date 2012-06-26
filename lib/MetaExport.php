<?php
/*
 * Generate metadata
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package SimpleSAMLphp
 * @subpackeage JANUS
 * @version $Id$
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class sspmod_janus_MetaExport
{
    const FLATFILE = '__FLAT_FILE_METADATA__';
    
    const XML = '__XML_METADATA__';
    
    const XMLREADABLE = '__XML_READABLE_METADATA__';

    const PHPARRAY = '__PHP_ARRAY_METADATA__';

    private static $_error;

    public static function getError()
    {
        return self::$_error;
    }

    public static function getPHPArrayMetadata($eid, $revision, array $option = null)
    {
        return self::getMetadata($eid, $revision, self::PHPARRAY, $option);
    }

    public static function getFlatMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::FLATFILE, $option);
    }
    
    public static function getXMLMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::XML, $option);
    }

    public static function getReadableXMLMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::XMLREADABLE, $option);
    }

    private static function getMetadata($eid, $revision, $type = null, array $option = null)
    {
        assert('ctype_digit($eid)');
        assert('ctype_digit($revision)');

        $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_janus_EntityController($janus_config);

        if(!$entity = $entityController->setEntity($eid, $revision)) {
            self::$_error = array('Entity could not be loaded - Eid: ' . $eid . ' Revisionid: ' . $revisionid);
            return false;
        }

        $metadata_raw = $entityController->getMetadata();

        // Get metadata fields
        $nm_mb = new sspmod_janus_MetadatafieldBuilder(
            $janus_config->getArray('metadatafields.' . $entity->getType())
        );
        $metadatafields_required = $nm_mb->getMetadatafields();

        // Get required metadata fields
        $required = array();
        foreach($metadatafields_required AS $mf) {
            if(isset($mf->required) && $mf->required === true) {
                $required[] = $mf->name;
            }
        }

        // Get metadata to me tested
        $metadata = array();
        foreach($metadata_raw AS $k => $v) {
            // Metadata field not defined
            if (!isset($metadatafields_required[$v->getKey()])) {
                continue;
            }
            // Value not set for metadata
            if (is_string($v->getValue()) && $v->getValue() == '') {
                continue;
            }

            // Compute is the default values is allowed
            $default_allow = false;
            if(isset($metadatafields_required[$v->getKey()]->default_allow) && is_bool($metadatafields_required[$v->getKey()]->default_allow)) {
                $default_allow = $metadatafields_required[$v->getKey()]->default_allow;
            }

            /*
             * Do not include metadata if value is set to default and default
             * is not allowed.
             */ 
            if (!$default_allow && (isset($metadatafields_required[$v->getKey()]->default) && ($v->getValue() == $metadatafields_required[$v->getKey()]->default))) {
                continue;
            }

            $metadata[] = $v->getKey();
        }

        // Compute missing metadata that is required
        $missing_required = array_diff($required, $metadata);
        
        $entityId = $entity->getEntityid();
        
        if (!empty($missing_required)) {
            SimpleSAML_Logger::error('JANUS - Missing required metadata fields. Entity_id:' . $entityId);
            self::$_error = $missing_required;
            return false;
        }

        try {
            $metaArray = $entityController->getMetaArray();
            $metaArray['eid'] = $eid;

            $blockedEntities = $entityController->getBlockedEntities();
            $allowedEntities = $entityController->getAllowedEntities();
            $disabledConsent = $entityController->getDisableConsent();

            $metaFlat = '// Revision: '. $entity->getRevisionid() ."\n";
            $metaFlat .= var_export($entityId, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

            // Add authproc filter to block blocked entities
            if (!empty($blockedEntities) || !empty($allowedEntities)) {
                $metaFlat = substr($metaFlat, 0, -2);

                if (!empty($allowedEntities)) {
                    $metaFlat .= "  'allowed' => array(\n";
                    $metaArray['allowed'] = array();
                    foreach($allowedEntities AS $allowedEntity) {
                        $metaFlat .= "      '". $allowedEntity['remoteentityid'] ."',\n";
                        $metaArray['allowed'][] = $allowedEntity['remoteentityid'];
                    }
                    $metaFlat .= "  ),\n";
                }

                if (!empty($blockedEntities)) {
                    $metaFlat .= "  'blocked' => array(\n";
                    $metaArray['blocked'] = array();
                    foreach($blockedEntities AS $blockedEntity) {
                        $metaFlat .= "    '". $blockedEntity['remoteentityid'] ."',\n";
                        $metaArray['blocked'][] = $blockedEntity['remoteentityid'];
                    }
                    $metaFlat .= "  ),\n";
                }

                $metaFlat .= '),';
            }

            // Add disable consent
            if (!empty($disabledConsent)) {
                $metaFlat = substr($metaFlat, 0, -2);
                $metaFlat .= "  'consent.disable' => array(\n";

                foreach($disabledConsent AS $key => $value) {
                    $metaFlat .= "    '". $key ."',\n";
                }

                $metaFlat .= "  ),\n";
                $metaFlat .= '),';
            }

            $maxCache = isset($option['maxCache']) ? $option['maxCache'] : null;
            $maxDuration = isset($option['maxDuration']) ? $option['maxDuration'] : null;

            try {
                $metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($entityId, $maxCache, $maxDuration);
                $metaBuilder->addMetadata($metaArray['metadata-set'], $metaArray);
            } catch (Exception $e) {
                SimpleSAML_Logger::error('JANUS - Entity_id:' . $entityId . ' - Error generating XML metadata - ' . var_export($e, true));
                self::$_error = array('Error generating XML metadata - ' . $e->getMessage());
                return false;
            }

            // Add organization info
            if(    !empty($metaArray['OrganizationName'])
                && !empty($metaArray['OrganizationDisplayName'])
                && !empty($metaArray['OrganizationURL'])
            ) {
                $metaBuilder->addOrganizationInfo(
                    array(
                        'OrganizationName' => $metaArray['OrganizationName'],
                        'OrganizationDisplayName' => $metaArray['OrganizationDisplayName'],
                        'OrganizationURL' => $metaArray['OrganizationURL']
                    )
                );
            }

            // Add contact info
            if(!empty($metaArray['contact'])) {
                $metaBuilder->addContact('technical', $metaArray['contact']);
            }

            switch($type) {
                case self::XML:
                    return $metaBuilder->getEntityDescriptor();
                case self::XMLREADABLE:
                    return $metaBuilder->getEntityDescriptorText();
                case self::PHPARRAY:
                    return $metaArray;
                case self::FLATFILE:
                default:
                    return $metaFlat;
            }
        } catch(Exception $exception) {
            $session = SimpleSAML_Session::getInstance();
            SimpleSAML_Utilities::fatalError($session->getTrackID(), 'JANUS - Metadatageneration', $exception);
            return false;
        }
    }
}
