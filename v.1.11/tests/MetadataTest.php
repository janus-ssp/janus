<?php
require('../../lib/SimpleSAML/Configuration.php');
require('../../lib/SimpleSAML/Error/Exception.php');
require('./lib/Database.php');
require('./lib/Metadata.php');

class MetadataTest extends PHPUnit_Framework_TestCase
{
    protected $metadata;

    protected function setUp()
    {
        $config = array(
            'dsn'       => 'mysql:host=localhost;dbname=janus_db',
            'username'  => 'janus',
            'password'  => 'janus_password', 
            'prefix'    => 'janus__',
        );

        $this->metadata = new sspmod_janus_Metadata($config);
    }

    /**
     * @dataProviderds eidProvider
     */
    public function testSetGetEid()
    {
        $this->metadata->setEid(3);
        $this->assertEquals(3, $this->metadata->getEid());
        
        return $this->metadata;
    }

    /**
     * @depends testSetGetEid
     */
    public function testModifiedType($metadata)
    {
        $this->assertInstanceOf('sspmod_janus_Metadata', $metadata);
    }

    public function eidProvider()
    {
        return array(
            array(1,1),    
            array(2,2),    
            array(3,3),    
            array(4,4),    
            array(5,5),    
            array(6,6),    
        );
    }
}   
