<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection;

use PHPUnit_Framework_TestCase;
use Phake;

use sspmod_janus_Metadata;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

class EntityTest extends PHPUnit_Framework_TestCase
{
    public function testConvertsMetadatatoDtoWhenSaving()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array()
            )
        ));

        // Create entity model to be tested (needs to be partial mocked due to service not being injected)
        $entityModel = Phake::partialMock('sspmod_janus_Entity', $config);
        $entityModel->setEntityid('fooConnection');
        $entityModel->setType('saml20_idp');


        // Mock connection serivce and save method
        $connectionServiceMock = Phake::mock('Janus\ServiceRegistry\Service\ConnectionService');
        Phake::when($entityModel)->getConnectionService()->thenReturn($connectionServiceMock);
        $resultConnection = new Connection('fooConnection', 'saml20-idp');
        Phake::when($connectionServiceMock)->save(Phake::anyParameters())->thenReturn($resultConnection);

        // Save connection
        $metadataCollection = array(
            new sspmod_janus_Metadata(null, 'foo:bar:baz', 1)
        );
        $entityModel->save($metadataCollection);

        // Verify metadata was converted to a nested Dto before saving
        $expectedConnectionDto = new ConnectionDto();
        Phake::verify($connectionServiceMock)->save(Phake::capture($expectedConnectionDto), true);
        $nestedMetadata = $expectedConnectionDto->metadata;
        $this->assertEquals(1, $nestedMetadata['foo']['bar']['baz']);
    }
}