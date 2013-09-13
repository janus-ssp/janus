<?php
class sspmod_janus_Model_EntityMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_Entity
     */
    private $entity;

    /**
     * @var sspmod_janus_Model_Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->entity = Phake::mock('sspmod_janus_Model_Entity');
    }

    public function testInstantiation()
    {
        $metadata = new sspmod_janus_Model_Entity_Metadata(
            $this->entity,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Metadata', $metadata);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new sspmod_janus_Model_Entity_Metadata(
            $this->entity,
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
        new sspmod_janus_Model_Entity_Metadata(
            $this->entity,
            'testKey',
            null
        );

    }
}