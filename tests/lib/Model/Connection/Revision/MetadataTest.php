<?php
class sspmod_janus_Model_ConnectionMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_Connection_Revision
     */
    private $connectionRevision;

    /**
     * @var sspmod_janus_Model_Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->connectionRevision = Phake::mock('sspmod_janus_Model_Connection_Revision');
    }

    public function testInstantiation()
    {
        $metadata = new sspmod_janus_Model_Connection_Revision_Metadata(
            $this->connectionRevision,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision_Metadata', $metadata);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new sspmod_janus_Model_Connection_Revision_Metadata(
            $this->connectionRevision,
            null,
            'testValue'
        );

    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid value ''
     */
    public function testInstantiationFailsWithInvalidValue()
    {
        new sspmod_janus_Model_Connection_Revision_Metadata(
            $this->connectionRevision,
            'testKey',
            null
        );

    }
}