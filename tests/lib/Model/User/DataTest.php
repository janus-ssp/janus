<?php
class sspmod_janus_Model_User_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_User
     */
    private $user;


    public function setUp()
    {
        $this->user = Phake::mock('sspmod_janus_Model_User');
    }

    public function testInstantiation()
    {
        $data = new sspmod_janus_Model_User_Data(
            $this->user,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('sspmod_janus_Model_User_Data', $data);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionData  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new sspmod_janus_Model_User_Data(
            $this->user,
            null,
            'testValue',
            'testSubscription'
        );

    }

    /**
     * @expectedException Exception
     * @expectedExceptionData  Invalid value ''
     */
    public function testInstantiationFailsWithInvalidValue()
    {
        new sspmod_janus_Model_User_Data(
            $this->user,
            'testKey',
            null,
            'testSubscription'
        );

    }
}