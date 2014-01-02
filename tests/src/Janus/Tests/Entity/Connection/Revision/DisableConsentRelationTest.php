<?php
namespace Janus\Tests\Entity\Connection\Revision;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\Connection\Revision\DisableConsentRelation;

class DisableConsentRelationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $connectionRevision = Phake::mock('Janus\Entity\Connection\Revision');
        $remoteConnection = Phake::mock('Janus\Entity\Connection');

        $disableConsentRelation = new DisableConsentRelation(
            $connectionRevision,
            $remoteConnection
        );

        $this->assertInstanceOf('Janus\Entity\Connection\Revision\DisableConsentRelation', $disableConsentRelation);
    }
}