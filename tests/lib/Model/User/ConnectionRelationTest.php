<?php
class sspmod_janus_Model_User_ConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = Phake::mock('sspmod_janus_Model_User');
        $connection = Phake::mock('sspmod_janus_Model_Connection');

        $connectionRelation = new sspmod_janus_Model_User_ConnectionRelation(
            $user,
            $connection
        );

        $this->assertInstanceOf('sspmod_janus_Model_User_ConnectionRelation', $connectionRelation);
    }
}