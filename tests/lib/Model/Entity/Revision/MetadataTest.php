<?php
class sspmod_janus_Model_EntityMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_Entity_Revision
     */
    private $entityRevision;

    /**
     * @var sspmod_janus_Model_Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->entityRevision = Phake::mock('sspmod_janus_Model_Entity_Revision');
    }

    public function testInstantiation()
    {
        $metadata = new sspmod_janus_Model_Entity_Revision_Metadata(
            $this->entityRevision,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Metadata', $metadata);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new sspmod_janus_Model_Entity_Revision_Metadata(
            $this->entityRevision,
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
        new sspmod_janus_Model_Entity_Revision_Metadata(
            $this->entityRevision,
            'testKey',
            null
        );

    }
}