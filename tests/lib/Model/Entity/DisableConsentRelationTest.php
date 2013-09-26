<?php

class sspmod_janus_Model_Entity_DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $remoteEntityId = Phake::mock('sspmod_janus_Model_Entity_Id');

        $disableConsentRelation = new sspmod_janus_Model_Entity_DisableConsentRelation(
            $entity,
            $remoteEntityId
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_DisableConsentRelation', $disableConsentRelation);
    }
}