<?php
class sspmod_janus_Model_User_MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_User
     */
    private $user;

    /**
     * @var sspmod_janus_Model_User
     */
    private $user2;

    public function setUp()
    {
        $this->user = Phake::mock('sspmod_janus_Model_User');
        $this->user2 = Phake::mock('sspmod_janus_Model_User');
    }

    public function testInstantiation()
    {
        $message = new sspmod_janus_Model_User_Message(
            $this->user,
            'testSubject',
            $this->user2,
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
            $this->user,
            null,
            $this->user2,
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
            $this->user,
            'testSubject',
            $this->user2,
            null
        );
    }
}