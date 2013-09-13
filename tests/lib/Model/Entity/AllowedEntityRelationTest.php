<?php
class sspmod_janus_Model_Entity_AllowedEntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $allowedEntityRelation = new sspmod_janus_Model_Entity_AllowedEntityRelation(
            $entity,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_AllowedEntityRelation', $allowedEntityRelation);
    }
}