<?php
namespace Janus\Test\Value;

use PHPUnit_Framework_TestCase;

use Janus\ServiceRegistry\Value\Ip;

class IpTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = new Ip(
            '127.0.0.1'
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Value\Ip', $user);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  IP '123' is not a valid address
     */
    public function testInstantiationFailsWithInvalidAddress()
    {
        new Ip(
            123
        );
    }
}