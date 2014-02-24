<?php
namespace Janus\Tests\ServiceRegistry\Entity;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $createdByUser = new User(
            'testUsername',
            array('admin')
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\User', $createdByUser);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Invalid username ''
     */
    public function testInstantiationFailsWithInvalidName()
    {
        new User(
            null,
            array('admin')
        );
    }
}