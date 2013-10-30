<?php
class sspmod_janus_Model_Entity_Revision_BlockedEntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityRevision = Phake::mock('sspmod_janus_Model_Entity_Revision');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $blockedEntityRelation = new sspmod_janus_Model_Entity_Revision_BlockedEntityRelation(
            $entityRevision,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_BlockedEntityRelation', $blockedEntityRelation);
    }
}