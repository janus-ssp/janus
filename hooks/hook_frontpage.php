<?php
/**
 * File containing a frontpage hook
 *
 * @author Jacob Christiaansen, <jach@wayf.dk>
 * @package simpleSMALphp
 * @subpackage JANUS
 * @varsion $Id$
 */
/**
 * Hook to add the JANUS module to the frontpage.
 *
 * @param array &$links  The links on the frontpage, split into sections.
 */
function janus_hook_frontpage(&$links) {
	assert('is_array($links)');
	assert('array_key_exists("links", $links)');

	$links['links'][] = array(
		'href' => SimpleSAML_Module::getModuleURL('janus/index.php'),
		'text' => array('en' => 'JANUS module'),
	);

}
?>
