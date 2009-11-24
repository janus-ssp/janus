<?php
abstract class sspmod_janus_Exporter
{
    /**
     * Remember to implement chacks that ensures that all required options is 
     * available.
     */
    abstract protected function __construct(array $option = null);

    abstract public function export($data);

    final public static function getInstance($type, array $option = null)
    {
        assert('is_string($type)');
        assert('is_array($option) || is_null($option)'); 

        // Resolve classname of exporter
        try {
            $className = SimpleSAML_Module::resolveClass($type, 'Exporter', 'sspmod_janus_Exporter');
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
