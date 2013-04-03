<?php
class sspmod_janus_Exporter_FTP extends sspmod_janus_Exporter
{
    private $_host;

    private $_port = 21;

    private $_path;

    private $_username;

    private $_password;

    private $_resource;

    protected function __construct(array $option)
    {
        if(!isset($option['host']))
        {
           throw new Exception('Host got given'); 
        } else {
            $this->_host = $option['host'];
        }

        if(!isset($option['path']))
        {
           throw new Exception('Path got given'); 
        } else {
            $this->_path = $option['path'];
        }

        if(!isset($option['username']))
        {
           throw new Exception('Username got given'); 
        } else {
            $this->_username = $option['username'];
        }
        
        if(!isset($option['password']))
        {
           throw new Exception('Password got given'); 
        } else {
            $this->_password = $option['password'];
        }

        if(isset($option['port']))
        {
            $this->_port = $option['port'];
        }

        if(!$this->_resource = fopen('ftp://'.$this->_username.':'.$this->_password.'@'.$this->_host.':'.$this->_port . $this->_path, 'w'))
        {
            throw new Exception('Could not connect to FTP.');
        }
     }

    public function export($data) 
    {

        if(!fwrite($this->_resource, $data))
        {
            throw new Exception('Fail to write data to FTP.');
        }
        fclose($this->_resource);
    }
}
