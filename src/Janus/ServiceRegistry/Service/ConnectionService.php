<?php

namespace Janus\ServiceRegistry\Service;

use Exception;
use Janus\ServiceRegistry\Command\FindConnectionRevisionCommand;
use Janus\ServiceRegistry\Connection\ConnectionDtoCollection;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataTreeFlattener;
use Janus\ServiceRegistry\Entity\ConnectionRepository;
use Monolog\Logger;
use PDOException;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\DBAL\DBALException;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Entity\User\ConnectionRelation;
use Janus\ServiceRegistry\Entity\Connection\ConnectionExistsException;
use Janus\ServiceRegistry\Connection\ConnectionDto;

/**
 * Service layer for all kinds of connection related logic
 *
 * Class Janus\ServiceRegistry\Service\ConnectionService
 */
class ConnectionService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * JANUS configuration
     * @var ConfigProxy
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MetadataTreeFlattener
     */
    private $metadataTreeFlattener;

    /**
     * @var MetadataDefinitionHelper
     */
    private $metadataDefinitionHelper;

    /**
     * @var ConnectionRepository
     */
    private $connectionRepository;

    /**
     * @param EntityManager $entityManager
     * @param ConfigProxy $config
     * @param Logger $logger
     * @param MetadataTreeFlattener $metadataTreeFlattener
     * @param MetadataDefinitionHelper $metadataDefinitionHelper
     * @param ConnectionRepository $connectionRepository
     */
    public function __construct(
        EntityManager $entityManager,
        ConfigProxy $config,
        Logger $logger,
        MetadataTreeFlattener $metadataTreeFlattener,
        MetadataDefinitionHelper $metadataDefinitionHelper,
        ConnectionRepository $connectionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->logger = $logger;
        $this->metadataTreeFlattener = $metadataTreeFlattener;
        $this->metadataDefinitionHelper = $metadataDefinitionHelper;
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Find a connection
     *
     * @param int $id
     * @return Connection
     * @throws Exception
     */
    public function findById($id)
    {
        /** @var ConnectionRepository $connectionRepository */
        $connectionRepository = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\Connection');
        return $connectionRepository->find($id);
    }

    /**
     * Finds the latest revision number for a given connection id
     *
     * @param FindConnectionRevisionCommand $command
     * @return int|null
     */
    public function findLatestRevisionNr(FindConnectionRevisionCommand $command)
    {
        return $this->connectionRepository->findLatestRevisionNr($command);
    }

    /**
     * @param $eid
     * @return array
     */
    public function findRevisionsByEid($eid, $limit = null, $offset = null)
    {
        /** @var Connection\RevisionRepository $revisionRepository */
        $revisionRepository = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\Connection\Revision');
        return $revisionRepository->findBy(
            array(
                'connection' => $eid
            ),
            array('revisionNr' => 'DESC'),
            $limit,
            $offset
        );
    }

    /**
     * Find the latest revisions of all connections.
     *
     * @return ConnectionDtoCollection
     */
    public function findAll()
    {
        return $this->findWithFilters();
    }

    /**
     * Find the latest revisions of all connections that match the given filters and return them as Dto's
     *
     * @param array $filter
     * @param string|null $sortBy
     * @param string $sortOrder
     * @return ConnectionDtoCollection
     */
    public function findWithFilters(
        array $filter = array(),
        $sortBy = null,
        $sortOrder = 'DESC'
    )
    {
        /** @var $sortFieldDefaultValue string */
        $sortFieldName = $this->config->getString('entity.prettyname', NULL);
        $revisions = $this->connectionRepository->findLatestRevisionsWithFilters($filter, $sortBy, $sortOrder, $sortFieldName);

        $metadataDefinitionHelper = $this->metadataDefinitionHelper;

        $dtos = array();
        $i = 0;
        while ($revision = array_shift($revisions)) {
            $dtos[] = $revision->toDto($metadataDefinitionHelper);

            // Done this this revision, Entity Manager and PHP in general please forget it now.
            $this->entityManager->detach($revision);
            unset($revision);

            // Every 100 entities clear the entity memory to reduce memory usage for 1100 entities from 270Mb to 100Mb.
            if ($i++ % 100 === 0) {
                $this->clearEntities();
            }
        }
        $this->clearEntities();
        return new ConnectionDtoCollection($dtos);
    }

    public function findDescriptorsForFilters($filter, $sortBy, $sortOrder)
    {
        /** @var $sortFieldDefaultValue string */
        $sortFieldName = $this->config->getString('entity.prettyname', NULL);
        $revisions = $this->connectionRepository->findLatestRevisionsWithFilters($filter, $sortBy, $sortOrder, $sortFieldName);

        $connectionDescriptors = array();
        foreach ($revisions as $revision) {
            $connectionDescriptors[] = $revision->toDescriptorDto();
        }
        return new ConnectionDtoCollection($connectionDescriptors);
    }

    /**
     * Grants a user permission to a given entity
     *
     * @param Connection  $connection
     * @param User $user
     */
    public function allowAccess(Connection $connection, User $user)
    {
        $userConnectionRelation = new ConnectionRelation(
            $user,
            $connection
        );

        $this->entityManager->persist($userConnectionRelation);
        $this->entityManager->flush();
    }

    /**
     * @param null $type
     * @return ConnectionDto
     */
    public function createDefaultDto($type = null)
    {
        $dto = new ConnectionDto();
        // @todo get from config
        $dto->state= 'testaccepted';
        $dto->isActive = true;
        $dto->allowAllEntities= true;
        $dto->type = $type;

        return $dto;
    }

    /**
     * Creates a new connection and/or revision from a data transfer object.
     *
     * @param ConnectionDto $dto
     * @return Connection
     * @throws DBALException
     * @throws Exception
     * @throws ConnectionExistsException
     */
    public function save(ConnectionDto $dto, $ignoreMissingDefinition = false)
    {
        $entityManager = $this->entityManager;

        $entityManager->getConnection()->beginTransaction();

        $connection = $this->createConnection(
            $dto->name,
            $dto->type,
            $dto->id
        );

        // Create new revision
        $connection->update(
            $this->metadataDefinitionHelper,
            $dto->name,
            $dto->type,
            $dto->parentRevisionNr,
            $dto->revisionNote,
            $dto->state,
            $dto->expirationDate,
            $dto->metadataUrl,
            $dto->allowAllEntities,
            $dto->arpAttributes,
            $dto->manipulationCode,
            $dto->isActive,
            $dto->notes
        );

        // Update connection and new revision
        $entityManager->persist($connection);
        $entityManager->flush($connection);

        $latestRevision = $connection->getLatestRevision();
        foreach ($this->disassembleConnectionReferences($dto->allowedConnections) as $referencedConnection) {
            $latestRevision->allowConnection($referencedConnection);
        }

        foreach ($this->disassembleConnectionReferences($dto->blockedConnections) as $referencedConnection) {
            $latestRevision->blockConnection($referencedConnection);
        }

        foreach ($this->disassembleConnectionReferences($dto->disableConsentConnections) as $referencedConnection) {
            $latestRevision->disableConsentForConnection($referencedConnection);
        }

        // Update connection and new revision
        $entityManager->persist($connection);


        try {
            $entityManager->flush($connection);
        } catch (DBALException $ex) {
            $pdoException = $ex->getPrevious();
            if (!$pdoException instanceof PDOException) {
                if ($pdoException->getCode() == 23000) {
                    throw new ConnectionExistsException($pdoException->getMessage());
                }
            }
            throw $ex;
        }

        // Store metadata
        $flatMetadata = array();
        if ($dto->metadata) {
            $flatMetadata = $this->metadataTreeFlattener->flatten($dto->metadata, $dto->type, $ignoreMissingDefinition);
        }

        $latestRevision = $connection->getLatestRevision();
        foreach ($flatMetadata as $key => $value) {
            // Note that empty values are no longer saved
            if ($value === null || $value === '') {
                continue;
            }
            $metadataRecord = new Metadata(
                $latestRevision,
                $key,
                $value
            );
            $entityManager->persist($metadataRecord);
        }
        $entityManager->flush();

        $entityManager->getConnection()->commit();
        $entityManager->clear();

        return $this->findById($connection->getId());
    }

    protected function disassembleConnectionReferences(array $references)
    {
        $connections = array();
        foreach ($references as $reference) {
            $connection = $this->findById($reference['id']);

            if (!$connection) {
                throw new \InvalidArgumentException(
                    'Referenced connection with id ' . $reference['id'] . ' does not exist'
                );
            }

            $connections[] = $connection;
        }
        return $connections;

    }

    /**
     * @param string $name
     * @param string $type
     * @param int $id
     * @return Connection
     */
    private function createConnection(
        $name,
        $type,
        $id = null
    )
    {
        $isNewConnection = empty($id);
        if ($isNewConnection) {
            $connection = new Connection($name, $type);
            return $connection;
        }

        return $this->findById($id);
    }

    /**
     * Deletes a connection and all of it's relations by id
     *
     * @param int $id
     * @throws Exception
     */
    public function deleteById($id)
    {
        $this->connectionRepository->deleteById($id);
    }

    private function clearEntities()
    {
        $this->entityManager->clear();
        gc_collect_cycles();
    }
}
