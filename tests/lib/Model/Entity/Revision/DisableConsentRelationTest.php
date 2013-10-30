<?php

class sspmod_janus_Model_Entity_Revision_DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entityRevision = Phake::mock('sspmod_janus_Model_Entity_Revision');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $disableConsentRelation = new sspmod_janus_Model_Entity_Revision_DisableConsentRelation(
            $entityRevision,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_DisableConsentRelation', $disableConsentRelation);
    }
}