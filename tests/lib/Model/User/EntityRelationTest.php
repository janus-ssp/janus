<?php
class sspmod_janus_Model_User_EntityRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = Phake::mock('sspmod_janus_Model_User');
        $entity = Phake::mock('sspmod_janus_Model_Entity');

        $entityRelation = new sspmod_janus_Model_User_EntityRelation(
            $user,
            $entity
        );

        $this->assertInstanceOf('sspmod_janus_Model_User_EntityRelation', $entityRelation);
    }
}