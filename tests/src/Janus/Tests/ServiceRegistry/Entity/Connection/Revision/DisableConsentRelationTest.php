<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection\Revision;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\Connection\Revision\DisableConsentRelation;

class DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('Janus\ServiceRegistry\Entity\Connection\Revision');
        $remoteConnection = Phake::mock('Janus\ServiceRegistry\Entity\Connection');

        $disableConsentRelation = new DisableConsentRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision\DisableConsentRelation', $disableConsentRelation);
    }
}