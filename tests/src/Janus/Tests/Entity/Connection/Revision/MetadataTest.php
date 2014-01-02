<?php
namespace Janus\Tests\Entity;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\Connection\Revision;
use Janus\Value\Ip;

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
        $this->connectionRevision = Phake::mock('Janus\Entity\Connection\Revision');
    }

    public function testInstantiation()
    {
        $metadata = new Revision\Metadata(
            $this->connectionRevision,
            'testKey',
            'testValue'
        );

        $this->assertInstanceOf('Janus\Entity\Connection\Revision\Metadata', $metadata);
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