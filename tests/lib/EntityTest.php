<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection;

use Janus\ServiceRegistry\Service\ConnectionService;
use PHPUnit_Framework_TestCase;
use Phake;

use sspmod_janus_Metadata;

use sspmod_janus_Entity;
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

class EntityTest extends PHPUnit_Framework_TestCase
{
    public function testConvertsMetadatatoDtoWhenSaving()
    {
        $entityModel = $this->createIdpEntity();
        $connectionServiceMock = $this->createConnectionServiceMock($entityModel);

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

    public function testConvertsAllowAllEntitiesYesToTrueWhenSaving()
    {
        $entityModel = $this->createIdpEntity();
        $entityModel->setAllowedAll('yes');

        $connectionServiceMock = $this->createConnectionServiceMock($entityModel);

        $entityModel->save(array());

        // Verify metadata was converted to a nested Dto before saving
        $expectedConnectionDto = new ConnectionDto();
        Phake::verify($connectionServiceMock)->save(Phake::capture($expectedConnectionDto), true);
        $this->assertTrue($expectedConnectionDto->allowAllEntities);
    }

    public function testConvertsAllowAllEntitiesNoToFalseWhenSaving()
    {
        $entityModel = $this->createIdpEntity();
        $entityModel->setAllowedAll('no');
        $connectionServiceMock = $this->createConnectionServiceMock($entityModel);

        $entityModel->save(array());

        // Verify metadata was converted to a nested Dto before saving
        $expectedConnectionDto = new ConnectionDto();
        Phake::verify($connectionServiceMock)->save(Phake::capture($expectedConnectionDto), true);
        $this->assertFalse($expectedConnectionDto->allowAllEntities);
    }

    /**
     * @return sspmod_janus_Entity|\Phake_ITestDouble
     */
    private function createIdpEntity()
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

        return $entityModel;
    }

    /**
     * @return ConnectionService|\Phake_ITestDouble
     */
    private function createConnectionServiceMock(sspmod_janus_Entity $entityModel)
    {
        // Mock connection service and save method
        $connectionServiceMock = Phake::mock('Janus\ServiceRegistry\Service\ConnectionService');
        Phake::when($entityModel)->getConnectionService()->thenReturn($connectionServiceMock);
        $resultConnection = new Connection($entityModel->getEntityid(), str_replace('_', '-', $entityModel->getType()));
        Phake::when($connectionServiceMock)->save(Phake::anyParameters())->thenReturn($resultConnection);

        return $connectionServiceMock;
    }
}