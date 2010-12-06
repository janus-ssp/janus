<?php

/**
 *  * This script displays a page to the user, which requests that the user
 *   * authorizes the release of attributes.
 *    *
 *     * @package simpleSAMLphp
 *      * @version $Id$
 *       */

SimpleSAML_Logger::info('JANUS - Access blocked');

if (!array_key_exists('StateId', $_REQUEST)) {
    throw new SimpleSAML_Error_BadRequest('Missing required StateId query parameter.');
}

$id = $_REQUEST['StateId'];
$state = SimpleSAML_Auth_State::loadState($id, 'janus:accessblock');

$globalConfig = SimpleSAML_Configuration::getInstance();

$t = new SimpleSAML_XHTML_Template($globalConfig, 'janus:accessblock.php', 'janus:accessblock');
$t->data['stateid'] = array('StateId' => $id);
$t->show();


?>
