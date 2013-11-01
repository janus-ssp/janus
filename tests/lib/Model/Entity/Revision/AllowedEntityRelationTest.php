
<?php
class sspmod_janus_Model_Entity_Revision_AllowedEntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityRevision = Phake::mock('sspmod_janus_Model_Entity_Revision');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $allowedEntityRelation = new sspmod_janus_Model_Entity_Revision_AllowedEntityRelation(
            $entityRevision,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_AllowedEntityRelation', $allowedEntityRelation);
    }
}