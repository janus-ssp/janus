<?php
class sspmod_janus_Model_Entity_BlockedEntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $blockedEntityRelation = new sspmod_janus_Model_Entity_BlockedEntityRelation(
            $entity,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_BlockedEntityRelation', $blockedEntityRelation);
    }
}