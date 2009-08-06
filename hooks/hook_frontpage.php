<?php
/**
 * Frontpage hook for JANUS
 *
 * PHP version 5
 *
 * JANUS is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * JANUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with JANUS. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Hooks
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen 
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
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
 * @since      Release 1.0.0
 */
function Janus_Hook_frontpage(&$links)
{
    assert('is_array($links)');
    assert('array_key_exists("links", $links)');

    $links['links'][] = array(
        'href' => SimpleSAML_Module::getModuleURL('janus/index.php'),
        'text' => array('en' => 'JANUS module'),
    );
}
?>
