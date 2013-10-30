<?php
class sspmod_janus_Model_EntityTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        $entityRevision = new sspmod_janus_Model_Entity_Revision(
            $entity,
            sspmod_janus_Model_Entity_Revision::TYPE_IDP
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision', $entityRevision);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Unknown entity type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        $entity = Phake::mock('sspmod_janus_Model_Entity');
        new sspmod_janus_Model_Entity_Revision(
            $entity,
            'unknownType'
        );
    }
}