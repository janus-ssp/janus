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
 * This hook adds a link to JANUS to the frontapage of the local SimpleSAMLphp
 * installation.
 *
 * @param array &$links The links on the frontpage, split into sections
 *
 * @return void
 *
 * @since Function available since Release 1.0.0
 */
function Janus_Hook_frontpage(&$links)
{
    assert('is_array($links)');

    $links['federation'][] = array(
        'href' => SimpleSAML_Module::getModuleURL('janus/index.php'),
        'text' => array('en' => 'JANUS module'),
    );
}
?>
