<?php
/**
 * JANUS External Messenger
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2011 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $id$
 * @link       http://code.google.com/p/janus-ssp/
 */
/**
 * External messenger
 *
 * Abstract class to be used when implementing external messengers in JANUS. The 
 * developer should implement a constructor and the send function.
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Core
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2011 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/janus-ssp/
 */
abstract class sspmod_janus_Messenger
{
    /**
     * Create a new external messenger
     *
     * Remember to implement checks that ensures that all required options is 
     * available.
     *
     * @param array $option Options for the exporter
     */
    abstract protected function __construct(array $option = null);

    /**
     * Send message
     *
     * @param mixed $data Data to be exported
     *
     * @return mixed
     */
    abstract public function send(array $data);

    /**
     * Get en instance of the messenger
     *
     * @param string $type   The messenger type
     * @param array  $option Options for the messenger
     *
     * @return ssmod_janus_Messenger An instance
     */
    final public static function getInstance($type, array $option = null)
    {
        assert('is_string($type)');
        assert('is_array($option) || is_null($option)'); 

        // Resolve classname of messenger
        try {
            $className 
                = SimpleSAML_Module::resolveClass(
                    $type, 
                    'Messenger', 
                    'sspmod_janus_Messenger'
                );
            SimpleSAML_Logger::debug('External messenger class found: ' . $className);
        }
        catch(Exception $e) {
            SimpleSAML_Logger::debug('External messenger class not found: ' . $type);
            throw $e;
        }

        // Return new instance of the messenger
        return new $className($option);
    }
} 
