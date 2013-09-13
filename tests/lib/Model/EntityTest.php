<?php
class sspmod_janus_Model_EntityTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = new sspmod_janus_Model_Entity(
            1,
            sspmod_janus_Model_Entity::TYPE_IDP,
            'testEntityId'
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity', $entity);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Unknown entity type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        new sspmod_janus_Model_Entity(
            1,
            'unknownType',
            'testEntityId'
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid entityid ''
     */
    public function testInstantiationFailsWithInvalidName()
    {
        new sspmod_janus_Model_Entity(
            1,
            sspmod_janus_Model_Entity::TYPE_IDP,
            null
        );
    }
}