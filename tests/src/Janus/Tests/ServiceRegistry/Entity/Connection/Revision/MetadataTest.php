<?php
namespace Janus\Tests\ServiceRegistry\Entity;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\Component\ReadonlyEntities\Value\Ip;

class ConnectionMetadataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Revision
     */
    private $connectionRevision;

    /**
     * @var Ip
     */
    private $updatedFromIp;

    public function setUp()
    {
        $this->connectionRevision = Phake::mock('Janus\ServiceRegistry\Entity\Connection\Revision');
    }

    public function testInstantiation()
    {
        $metadata = new Revision\Metadata(
            $this->connectionRevision,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision\Metadata', $metadata);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Invalid key ''
     */
    public function testInstantiationFailsWithInvalidKey()
    {
        new Revision\Metadata(
            $this->connectionRevision,
            null,
            'testValue'
        );

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Invalid value ''
     */
    public function testInstantiationFailsWithInvalidValue()
    {
        new Revision\Metadata(
            $this->connectionRevision,
            'testKey',
            null
        );

    }
}