<?php

class sspmod_janus_Model_Entity_DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $remoteEntity = Phake::mock('sspmod_janus_Model_Entity');

        $disableConsentRelation = new sspmod_janus_Model_Entity_DisableConsentRelation(
            $entity,
            $remoteEntity
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_DisableConsentRelation', $disableConsentRelation);
    }
}