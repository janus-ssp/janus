<?php
namespace Janus\Tests\ServiceRegistry\Service;

use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataTreeFlattener;
use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\ConnectionRepository;
use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Service\ConnectionService;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;

class ConnectionServiceTest extends PHPUnit_Framework_TestCase
{
    public function testFlattensMetadataOnSave()
    {
        // Create Conection service
        $entityManagerMock = Phake::mock('Doctrine\ORM\EntityManager');
        $mockDbConnection = Phake::mock('Doctrine\DBAL\Connection');
        Phake::when($entityManagerMock)
            ->getConnection()
            ->thenReturn($mockDbConnection);
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'foo:bar:baz' => array(
                        'supported' => array(
                            1
                        )
                    )
                )
            )
        ));

        // Create metadata tree flattener
        $metadataDefinitionHelper = new MetadataDefinitionHelper($config);
        $metadataTreeFlattener = new MetadataTreeFlattener($metadataDefinitionHelper);

        $loggerMock = Phake::mock('Monolog\Logger');

        $connectionRepository = new ConnectionRepository($entityManagerMock, $loggerMock);

        $connectionService = new ConnectionService(
            $entityManagerMock,
            $config,
            $loggerMock,
            $metadataTreeFlattener,
            $metadataDefinitionHelper,
            $connectionRepository
        );

        // Mock existing connection
        $connectionRepositoryMock = Phake::mock('Janus\ServiceRegistry\Entity\ConnectionRepository');
        $connection = new Connection('fooConnection', 'saml20-idp', 'initial revision');
        Phake::when($connectionRepositoryMock)
            ->find(1)
            ->thenReturn($connection);
        Phake::when($entityManagerMock)
            ->getRepository('Janus\ServiceRegistry\Entity\Connection')
            ->thenReturn($connectionRepositoryMock);



        // Mock metadata dto
        $nestedMetadata = array(
            'foo' => array(
                'bar' => array(
                    'baz' => 1
                )
            )
        );

        // Save
        $connectionDto = new ConnectionDto();
        $connectionDto->setId(1);
        $connectionDto->setName('fooConnection');
        $connectionDto->setType('saml20-idp');
        $connectionDto->setRevisionNote('initial revision');
        $connectionDto->setMetadata($nestedMetadata);
        $connectionService->save($connectionDto);

        // Verify flattened metadata is saved
        $metadataRecord = new Metadata(
            $connection->getLatestRevision(),
            'foo:bar:baz',
            1
        );
        Phake::verify($entityManagerMock)->persist($metadataRecord);
    }

    public function testReturnsConnectionEntity()
    {
        // Mock repository which returns connection
        $connectionRepositoryMock = Phake::mock('Janus\ServiceRegistry\Entity\ConnectionRepository');
        $connection = new Connection('test', 'saml20-idp');
        Phake::when($connectionRepositoryMock)
            ->find(1)
            ->thenReturn($connection);

        // Mock entity manager which returns repository
        $entityManagerMock = Phake::mock('Doctrine\ORM\EntityManager');
        Phake::when($entityManagerMock)
            ->getRepository('Janus\ServiceRegistry\Entity\Connection')
            ->thenReturn($connectionRepositoryMock);

        // Create service
        $config = new ConfigProxy(array());
        $metadataDefinitionHelper = new MetadataDefinitionHelper($config);
        $loggerMock = Phake::mock('Monolog\Logger');
        $connectionService = new ConnectionService(
            $entityManagerMock,
            $config,
            $loggerMock,
            new MetadataTreeFlattener($metadataDefinitionHelper),
            $metadataDefinitionHelper,
            $connectionRepositoryMock
        );

        $connection = $connectionService->findById(1);
        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection', $connection);
    }
}