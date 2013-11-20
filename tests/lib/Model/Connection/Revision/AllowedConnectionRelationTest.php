
<?php
class sspmod_janus_Model_Connection_Revision_AllowedConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('sspmod_janus_Model_Connection_Revision');
        $remoteConnection = Phake::mock('sspmod_janus_Model_Connection');

        $allowedConnectionRelation = new sspmod_janus_Model_Connection_Revision_AllowedConnectionRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision_AllowedConnectionRelation', $allowedConnectionRelation);
    }
}