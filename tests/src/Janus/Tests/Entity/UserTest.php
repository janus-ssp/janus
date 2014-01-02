<?php
namespace Janus\Tests\Entity;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $createdByUser = new User(
            'testUsername',
            array('admin')
        );

        $this->assertInstanceOf('Janus\Entity\User', $createdByUser);
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