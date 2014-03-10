<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection\Revision;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\Connection\Revision\BlockedConnectionRelation;

class BlockedConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('Janus\ServiceRegistry\Entity\Connection\Revision');
        $remoteConnection = Phake::mock('Janus\ServiceRegistry\Entity\Connection');

        $blockedConnectionRelation = new BlockedConnectionRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision\BlockedConnectionRelation', $blockedConnectionRelation);
    }
}