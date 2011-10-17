<?php
/**
 * Frontpage hook for JANUS
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Hooks
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @author     pitbulk
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.0.0
 */
/**
 * Frontpage hook for JANUS
 *
 * This hook adds the following links to the 'Federation' tab of the local SimpleSAMLphp
 * installation:
 * - JANUS module
 * - Verify JANUS entities
 *
 * @param array &$links The links on the frontpage, split into sections
 *
 * @return void
 *
 * @since Function available since Release 1.0.0
 */
function janus_hook_frontpage(&$links)
{
    assert('is_array($links)');

    $links['federation'][] = array(
        'href' => SimpleSAML_Module::getModuleURL('janus/index.php'),
        'text' => array('en' => 'JANUS module'),
    );

    $links['federation'][] = array(
        'href' => SimpleSAML_Module::getModuleURL('janus/show-entities-validation.php'),
        'text' => array('en' => 'Verify JANUS Entities'),
    );
}