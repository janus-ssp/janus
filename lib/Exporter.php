<?php
/**
 * JANUS Metadata Exporter
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
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $$
 * @link       http://code.google.com/p/janus-ssp/
 */
/**
 * Metadata Exporter
 *
 * Abstract class to be used when implementing metadata exportes in JANUS. The 
 * developer should implement a constructor and the export function.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 */
abstract class sspmod_janus_Exporter
{
    /**
     * Create a new exporter
     *
     * Remember to implement checks that ensures that all required options is 
     * available.
     *
     * @param array $option Options for the exporter
     */
    abstract private function __construct(array $option = null);

    /**
     * Export metadata
     *
     * @param mixed $data Data to be exported
     *
     * @return mixed
     */
    abstract public function export($data);

    /**
     * Get en instance of the exporter
     *
     * @param string $type   The exporter type
     * @param array  $option Options for the exporter
     *
     * @return ssmod_janus_Exporter An instance
     */
    final public static function getInstance($type, array $option = null)
    {
        assert('is_string($type)');
        assert('is_array($option) || is_null($option)'); 

        // Resolve classname of exporter
        try {
            $className 
                = SimpleSAML_Module::resolveClass(
                    $type, 
                    'Exporter', 
                    'sspmod_janus_Exporter'
                );
            SimpleSAML_Logger::debug('External exporter class found: ' . $className);
        }
        catch(Exception $e) {
            SimpleSAML_Logger::debug('External exporter class not found: ' . $type);
            throw $e;
        }

        // Return new instance of exporter
        return new $className($option);
    }
} 
