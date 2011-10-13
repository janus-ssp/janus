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

ini_set('display_errors', true);
ini_set('max_execution_time', 2700); // Run for no more than 45 minutes

require __DIR__ . '/../www/_includes.php';
require __DIR__ . '/../lib/Cron/Job/Interface.php';
require __DIR__ . '/../lib/Cron/Job/Abstract.php';
require __DIR__ . '/../lib/Cron/Job/MetadataRefresh.php';
require __DIR__ . '/../lib/Cron/Logger.php';
require __DIR__ . '/../lib/Cron/Job/ValidateEntityCertificate.php';
require __DIR__ . '/../lib/Cron/Job/ValidateEntityEndpoints.php';

/**
 * Cron hook for JANUS
 *
 * This hook does the following:
 *
 * - Downloads the metadata of the entities registered in JANUS and
 *   update the entities with the new metadata.
 * - Validates all entity certificates
 * - Validates all entity endpoints
 *
 * @param array &$cronInfo The array with the tags and output summary of the cron run
 *
 * @return void
 *
 * @since Function available since Release 1.4.0
 */
function janus_hook_cron(&$cronInfo) {
    assert('is_array($cronInfo)');
    assert('array_key_exists("summary", $cronInfo)');
    assert('array_key_exists("tag", $cronInfo)');

    SimpleSAML_Logger::info('cron [janus]: Running cron in cron tag [' . $cronInfo['tag'] . '] ');

    // Refresh metadata
    $refresher = new sspmod_janus_Cron_Job_MetadataRefresh();
    $summaryLines = $refresher->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);

    // Validate entity signing certificates
    $validator = new sspmod_janus_Cron_Job_ValidateEntityCertificate();
    $summaryLines = $validator->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);

    // Validate entity endpoints
    $validator = new sspmod_janus_Cron_Job_ValidateEntityEndpoints();
    $summaryLines = $validator->runForCronTag($cronInfo['tag']);
    $cronInfo['summary'] = array_merge($cronInfo['summary'], $summaryLines);
}