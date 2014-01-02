<?php
namespace Janus\Tests\Entity\User;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\User;

class DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;


    public function setUp()
    {
        $this->user = Phake::mock('Janus\Entity\User');
    }

    public function testInstantiation()
    {
        $data = new User\Data(
            $this->user,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('Janus\Entity\User\Data', $data);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionData  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new User\Data(
            $this->user,
            null,
            'testValue',
            'testSubscription'
        );

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionData  Invalid value ''
     */
    public function testInstantiationFailsWithInvalidValue()
    {
        new User\Data(
            $this->user,
            'testKey',
            null,
            'testSubscription'
        );

    }
}