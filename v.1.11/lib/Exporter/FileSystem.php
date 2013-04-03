<?php
class sspmod_janus_Exporter_FileSystem extends sspmod_janus_Exporter
{
    private $_path;

    protected function __construct(array $option)
    {
        // Is path parsed as a string
        if(!isset($option['path']) || !is_string($option['path'])) {
            throw new Exception('Invalid path given for FileSystem exporter.' .
                ' Should be a string:' . 
                var_export($option['path'], true));
        }
        // Do the file exists in advance
        if(file_exists($option['path'])) {
            SimpleSAML_Logger::info('File: ' . $option['path'] . ' exists and will be overwritten');
        }

        // Is file writable
        if (!is_writable($option['path']))
        {
            throw new Exception('Path not writable:' . 
                var_export($option['path'], true));
        }

        $this->_path = $option['path'];
    }

    public function export($data) 
    {
        if(!file_put_contents($this->_path, $data))
        {
            throw new Exception('Data not written to path: ' . $this->_path);
        }
    }
}
