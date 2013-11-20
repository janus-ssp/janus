<?php

class sspmod_janus_Model_Connection_Revision_DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('sspmod_janus_Model_Connection_Revision');
        $remoteConnection = Phake::mock('sspmod_janus_Model_Connection');

        $disableConsentRelation = new sspmod_janus_Model_Connection_Revision_DisableConsentRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision_DisableConsentRelation', $disableConsentRelation);
    }
}