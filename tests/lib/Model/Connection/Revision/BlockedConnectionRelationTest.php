<?php
class sspmod_janus_Model_Connection_Revision_BlockedConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('sspmod_janus_Model_Connection_Revision');
        $remoteConnection = Phake::mock('sspmod_janus_Model_Connection');

        $blockedConnectionRelation = new sspmod_janus_Model_Connection_Revision_BlockedConnectionRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision_BlockedConnectionRelation', $blockedConnectionRelation);
    }
}