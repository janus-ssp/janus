<?php
namespace Janus\Tests\ServiceRegistry\Entity;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Entity\Connection;
use PHPUnit_Framework_TestCase;

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

        $configMock = new ConfigProxy(array(), 'a');
        $connection->update(
            new MetadataDefinitionHelper($configMock),
            'a',
            'saml20-idp',
            null,
            'testNote',
            'test',
            new \DateTime(),
            'testurl',
            true,
            array(),
            null,
            true,
            null
        );

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision', $connection->getLatestRevision());
    }
}