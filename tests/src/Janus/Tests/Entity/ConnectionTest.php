<?php
namespace Janus\Tests\Entity;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\Entity\Connection;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Name cannot be empty
     */
    public function testInstantiationWhenNameIsEmpty()
    {
        new Connection(
            '',
            'saml20-idp'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Unknown connection type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        new Connection(
            'test',
            'unknownType'
        );
    }

    public function testUpdatingCreatesRevision()
    {
        $connection = new Connection(
            'a',
            'saml20-idp'
        );

        $connection->update(
            'a',
            'saml20-idp',
            null,
            'testNote',
            'test',
            new \DateTime(),
            'testurl',
            true,
            null,
            null,
            true
        );

        $this->assertInstanceOf('Janus\Entity\Connection\Revision', $connection->getLatestRevision());
    }
}