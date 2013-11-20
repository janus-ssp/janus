<?php
class sspmod_janus_Model_User_MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_User
     */
    private $fromUser;

    /**
     * @var sspmod_janus_Model_User
     */
    private $subscribingUser;

    public function setUp()
    {
        $this->fromUser = Phake::mock('sspmod_janus_Model_User');
        $this->subscribingUser = Phake::mock('sspmod_janus_Model_User');
    }

    public function testInstantiation()
    {
        $message = new sspmod_janus_Model_User_Message(
            $this->fromUser,
            'testSubject',
            'testMessage',
            $this->subscribingUser,
            'testSubscription'
        );

        $this->assertInstanceOf('sspmod_janus_Model_User_Message', $message);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid subject ''
     */
    public function testInstantiationFailsWithInvalidSubject()
    {
        new sspmod_janus_Model_User_Message(
            $this->fromUser,
            null,
            'testMessage',
            $this->subscribingUser,
            'testSubscription'
        );

    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid message ''
     */
    public function testInstantiationFailsWithInvalidMessage()
    {
        new sspmod_janus_Model_User_Message(
            $this->fromUser,
            'testSubject',
            null,
            $this->subscribingUser,
            'testSubscription'
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid subscription ''
     */
    public function testInstantiationFailsWithInvalidSubscription()
    {
        new sspmod_janus_Model_User_Message(
            $this->fromUser,
            'testSubject',
            'testMessage',
            $this->subscribingUser,
            null
        );
    }
}