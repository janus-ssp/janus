<?php
/**
 * Cron hook for JANUS
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Hooks
 * @author     Sixto Martín <smartin@yaco.es>
 * @author     Lorenzo Gil <lgs@yaco.es>
 * @copyright  2009 Yaco Sistemas
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.4.0
 */
/**
 * Cron hook for JANUS
 *
 * This hook downloads the metadata of the entities registered in JANUS and
 * update the entities with the new metadata.
 *
 * @param array &$croninfo The array with the tags and output summary of the cron run
 *
 * @return void
 *
 * @since Function available since Release 1.4.0
 */
function janus_hook_cron(&$croninfo) {
    assert('is_array($croninfo)');
    assert('array_key_exists("summary", $croninfo)');
    assert('array_key_exists("tag", $croninfo)');

    SimpleSAML_Logger::info('cron [janus]: Running cron in cron tag [' . $croninfo['tag'] . '] ');

    try {
        $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

        $cron_tags = $janus_config->getArray('cron', array());
        $croninfo['summary'] = array();

        if (!in_array($croninfo['tag'], $cron_tags)) {
            return; // Nothing to do: it's not our time
        }

        $util = new sspmod_janus_AdminUtil();
        $entities = $util->getEntities();

        foreach ($entities as $partial_entity) {
            $mcontroller = new sspmod_janus_EntityController($janus_config);

            $eid = $partial_entity['eid'];
            if(!$mcontroller->setEntity($eid)) {
                $croninfo['summary'][] = 'Error during janus cron: failed import entity. Wrong eid. ' . $eid;
                continue;
            }

            $mcontroller->loadEntity();
            $entity = $mcontroller->getEntity();
            $entity_id = $entity->getEntityId();
            $metadata_url = $entity->getMetadataURL();

            if (empty($metadata_url)) {
                continue;
            }

            $xml = file_get_contents($metadata_url);
            if (!$xml) {
                $croninfo['summary'][] = 'Error during janus cron: failed import entity. Bad URL. ' . $entity_id;
                continue;
            }

            if($entity->getType() == 'saml20-sp') {
                if($mcontroller->importMetadata20SP($xml, $updated) !== 'status_metadata_parsed_ok') {
                    $croninfo['summary'][] = '<p>Entity: ' . $entity_id . ' not updated</p>';
                }
            } else if($entity->getType() == 'saml20-idp') {
                if($mcontroller->importMetadata20IdP($xml, $updated) !== 'status_metadata_parsed_ok') {
                    $croninfo['summary'][] = '<p>Entity: '. $entity_id . ' not updated</p>';
                }
            }
            else {
                $croninfo['summary'][] = '<p>Error during janus cron: failed import entity ' . $entity_id . '. Wrong type</p>';
            }

            if ($updated) {
                $entity->setParent($entity->getRevisionid());
                $mcontroller->saveEntity();
                $croninfo['summary'][] = '<p>Entity: ' . $entity_id . ' updated</p>';
            }
        }

    } catch (Exception $e) {
        $croninfo['summary'][] = 'Error during janus sync metadata: ' . $e->getMessage();
    }
}
?>
