<?php
namespace Janus\Tests\ServiceRegistry\Entity\User;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Value\Ip;

class SubscriptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->user = Phake::mock('Janus\ServiceRegistry\Entity\User');
        $this->updatedFromIp = Phake::mock('Janus\ServiceRegistry\Value\Ip');
    }

    public function testInstantiation()
    {
        $subscription = new User\Subscription(
            $this->user,
            'testSubscription',
            $this->updatedFromIp
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\User\Subscription', $subscription);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionSubscription  Invalid address ''
     */
    public function testInstantiationFailsWithInvalidAddress()
    {
        new User\Subscription(
            $this->user,
            null,
            $this->updatedFromIp
        );
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionSubscription  Invalid type ''
     */
    public function testInstantiationFailsWithInvalidType()
    {
        new User\Subscription(
            $this->user,
            null,
            $this->updatedFromIp
        );
    }
}