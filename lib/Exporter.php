<?php
/**
 * JANUS Metadata Exporter
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2009 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $$
 * @link       http://github.com/janus-ssp/janus/
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
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://github.com/janus-ssp/janus/
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
    abstract protected function __construct(array $option = null);

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
                = \SimpleSAML\Module::resolveClass(
                    $type, 
                    'Exporter', 
                    'sspmod_janus_Exporter'
                );
            \SimpleSAML\Logger::debug('External exporter class found: ' . $className);
        }
        catch(Exception $e) {
            \SimpleSAML\Logger::debug('External exporter class not found: ' . $type);
            throw $e;
        }

        // Return new instance of exporter
        return new $className($option);
    }
} 
