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

    private static $_error;

    public static function getError()
    {
        return self::$_error;
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
        $econtroller = new sspmod_janus_EntityController($janus_config);

        if(!$entity = $econtroller->setEntity($eid, $revision)) {
            return false;
        }

        $metadata_raw = $econtroller->getMetadata();

        $metadata_required = $janus_config->getArray('metadatafields.' . $entity->getType());

        foreach($metadata_required AS $k => $v) {
            if(array_key_exists('required', $v) && $v['required'] === true) {
                $required[] = $k;
            }
        }

        $metadata = array();
        foreach($metadata_raw AS $k => $v) {
            if (!isset($metadata_required[$v->getKey()])) {
                continue;
            }
            if ($v->getValue() == '') {
                continue;
            }

            $default_allow = false;
            if(isset($metadata_required[$v->getKey()]['default_allow']) && is_bool($metadata_required[$v->getKey()]['default_allow'])) {
                $default_allow = $metadata_required[$v->getKey()]['default_allow'];
            }

            if (!$default_allow && (isset($metadata_required[$v->getKey()]['default']) && ($v->getValue() == $metadata_required[$v->getKey()]['default']))) {
                continue;
            }

            $metadata[] = $v->getKey();
        }

        $missing_required = array_diff($required, $metadata);
        
        $entityid = $entity->getEntityid();
        
        if (empty($missing_required)) {
            try {
                $metaArray = $econtroller->getMetaArray();
                $metaArray['eid'] = $eid;

                $blocked_entities = $econtroller->getBlockedEntities();
                $allowed_entities = $econtroller->getAllowedEntities();
                $disable_consent = $econtroller->getDisableConsent();

                $metaflat = '// Revision: '. $entity->getRevisionid() ."\n";
                $metaflat .= var_export($entityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

                // Add authproc filter to block blocked entities
                if(!empty($blocked_entities) || !empty($allowed_entities)) {
                    $metaflat = substr($metaflat, 0, -2);
                    if(!empty($blocked_entities)) {
                        $metaflat .= "  'blocked' => array(\n";
                        foreach($blocked_entities AS $bentity => $value) {
                            $metaflat .= "    '". $bentity ."',\n";
                        }
                        $metaflat .= "  ),\n";
                    }
                    if(!empty($allowed_entities)) {
                        $metaflat .= "  'allowed' => array(\n";
                        foreach($allowed_entities AS $aentity => $value) {
                            $metaflat .= "      '". $aentity ."',\n";
                        }
                        $metaflat .= "  ),\n";
                    }
                    $metaflat .= '),';
                }

                // Add disable consent
                if(!empty($disable_consent)) {
                    $metaflat = substr($metaflat, 0, -2);
                    $metaflat .= "  'consent.disable' => array(\n";

                    foreach($disable_consent AS $key => $value) {
                        $metaflat .= "    '". $key ."',\n";
                    }

                    $metaflat .= "  ),\n";
                    $metaflat .= '),';
                }

                $maxCache = isset($option['maxCache']) ? $option['maxCache'] : null;
                $maxDuration = isset($option['maxDuration']) ? $option['maxDuration'] : null;
                

                $metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($entityid, $maxCache, $maxDuration);
                $metaBuilder->addMetadata($metaArray['metadata-set'], $metaArray);

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
                    case self::FLATFILE:
                    default:
                        return $metaflat;
                }
            } catch(Exception $exception) {
                $session = SimpleSAML_Session::getInstance();
                SimpleSAML_Utilities::fatalError($session->getTrackID(), 'JANUS - Metadatageneration', $exception);
            }
        }  else {
            SimpleSAML_Logger::error('JANUS - Missing required metadata fields. Entity_id:' . $entityid);
            self::$_error = $missing_required;
            return false;
        }
    }
}
