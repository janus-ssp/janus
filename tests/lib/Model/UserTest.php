<?php
class sspmod_janus_Model_UserTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $createdByUser = new sspmod_janus_Model_User(
            'testUsername',
            array('admin')
        );

        $this->assertInstanceOf('sspmod_janus_Model_User', $createdByUser);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid username ''
     */
    public function testInstantiationFailsWithInvalidName()
    {
        new sspmod_janus_Model_User(
            null,
            array('admin')
        );
    }
}