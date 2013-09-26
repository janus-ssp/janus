<?php
class sspmod_janus_Model_Entity_BlockedEntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $remoteEntityId = Phake::mock('sspmod_janus_Model_Entity_Id');

        $blockedEntityRelation = new sspmod_janus_Model_Entity_BlockedEntityRelation(
            $entity,
            $remoteEntityId
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_BlockedEntityRelation', $blockedEntityRelation);
    }
}