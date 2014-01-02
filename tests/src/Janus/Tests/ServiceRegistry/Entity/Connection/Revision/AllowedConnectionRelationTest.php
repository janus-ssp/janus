<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection\Revision;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\Connection\Revision\AllowedConnectionRelation;

class AllowedConnectionRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('Janus\ServiceRegistry\Entity\Connection\Revision');
        $remoteConnection = Phake::mock('Janus\ServiceRegistry\Entity\Connection');

        $allowedConnectionRelation = new AllowedConnectionRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision\AllowedConnectionRelation', $allowedConnectionRelation);
    }
}