<?php
namespace Janus\Tests\ServiceRegistry\Entity\User;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\User\ConnectionRelation;

class ConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = Phake::mock('Janus\ServiceRegistry\Entity\User');
        $connection = Phake::mock('Janus\ServiceRegistry\Entity\Connection');

        $connectionRelation = new ConnectionRelation(
            $user,
            $connection
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\User\ConnectionRelation', $connectionRelation);
    }
}