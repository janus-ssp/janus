<?php
class sspmod_janus_Model_EntityTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityId = Phake::mock('sspmod_janus_Model_Entity_Id');
        $entity = new sspmod_janus_Model_Entity(
            $entityId,
            sspmod_janus_Model_Entity::TYPE_IDP
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity', $entity);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Unknown entity type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        $entityId = Phake::mock('sspmod_janus_Model_Entity_Id');
        new sspmod_janus_Model_Entity(
            $entityId,
            'unknownType'
        );
    }
}