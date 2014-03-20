<?php
namespace Janus\Tests\ServiceRegistry\Entity\User;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\User;

class MessageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $fromUser;

    /**
     * @var User
     */
    private $subscribingUser;

    public function setUp()
    {
        $this->fromUser = Phake::mock('Janus\ServiceRegistry\Entity\User');
        $this->subscribingUser = Phake::mock('Janus\ServiceRegistry\Entity\User');
    }

    public function testInstantiation()
    {
        $message = new User\Message(
            $this->fromUser,
            'testSubject',
            'testMessage',
            $this->subscribingUser,
            'testSubscription'
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\User\Message', $message);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Invalid subject ''
     */
    public function testInstantiationFailsWithInvalidSubject()
    {
        new User\Message(
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
        new User\Message(
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
        new User\Message(
            $this->fromUser,
            'testSubject',
            'testMessage',
            $this->subscribingUser,
            null
        );
    }
}