<?php
namespace Janus\Tests\Entity\User;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\User\ConnectionRelation;

class ConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = Phake::mock('Janus\Entity\User');
        $connection = Phake::mock('Janus\Entity\Connection');

        $connectionRelation = new ConnectionRelation(
            $user,
            $connection
        );

        $this->assertInstanceOf('Janus\Entity\User\ConnectionRelation', $connectionRelation);
    }
}