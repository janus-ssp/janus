<?php
class sspmod_janus_Model_User_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_User
     */
    private $user;

    /**
     * @var sspmod_janus_Model_Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->user = Phake::mock('sspmod_janus_Model_User');
        $this->updatedFromIp = Phake::mock('sspmod_janus_Model_Ip');
    }

    public function testInstantiation()
    {
        $subscription = new sspmod_janus_Model_User_Subscription(
            $this->user,
            'testSubscription',
            $this->updatedFromIp
        );

        $this->assertInstanceOf('sspmod_janus_Model_User_Subscription', $subscription);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionSubscription  Invalid subscription ''
     */
    public function testInstantiationFailsWithInvalidSubscription()
    {
        new sspmod_janus_Model_User_Subscription(
            $this->user,
            null,
            $this->updatedFromIp
        );
    }
}