<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Service;

use Exception;
use Janus\ServiceRegistry\Entity\ConnectionRepository;
use Monolog\Logger;
use PDOException;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\NoResultException;
use Doctrine\DBAL\DBALException;

use SimpleSAML_Configuration;

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
     * @var SimpleSAML_Configuration
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EntityManager $entityManager
     * @param SimpleSAML_Configuration $config
     * @param Logger $logger
     */
    public function __construct(
        EntityManager $entityManager,
        SimpleSAML_Configuration $config,
        Logger $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->logger = $logger;
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
     * @param $eid
     * @return array
     */
    public function findRevisionsByEid($eid)
    {
        /** @var Connection\RevisionRepository $revisionRepository */
        $revisionRepository = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\Connection\Revision');
        return $revisionRepository->findBy(array(
                'connection' => $eid
            ), array('revisionNr' => 'DESC')
        );
    }

    /**
     * Find the latest revisions of all connections that match the given filters.
     *
     * @param array $filter
     * @param string $sortBy
     * @param string $sortOrder
     * @internal param string $state
     * @internal param string $stateExclude
     * @internal param bool $allowedUserId
     * @return Revision[]
     */
    public function findLatestRevisionsWithFilters(
        array $filter = array(),
        $sortBy = null,
        $sortOrder = 'DESC'
    )
    {
        $this->logger->info("Connection Service: Trying to get connections");

        $queryBuilder = $this->entityManager->createQueryBuilder();

        if ($sortBy == "created") {
            $sortFieldSql = 'CR.createdAtDate';
        } else {
            $sortFieldSql = 'coalesce(MD.value, CR.name)';
        }

        // Select entity (only last revision)
        $queryBuilder
            ->select(array(
                'CR',
                $sortFieldSql . ' AS HIDDEN orderfield'
            ))
            ->from('Janus\ServiceRegistry\Entity\Connection\Revision', 'CR')
            // Filter latest revision
            ->innerJoin(
                'CR.connection',
                'C',
                Expr\Join::WITH,
                'C.revisionNr = CR.revisionNr'
            );

        // Filter out entities that the current user may not see
        if (isset($filter['allowedUserId'])) {
            $queryBuilder
                ->innerJoin(
                    'C.userRelations',
                    'UCR',
                    Expr\Join::WITH,
                    $queryBuilder->expr()->like('IDENTITY(UCR.user)', ':userId')
                )
                ->setParameter(':userId', $filter['allowedUserId']);
        }

        // Include given workflow state
        if (isset($filter['state'])) {
            $queryBuilder
                ->andWhere('CR.state = :state')
                ->setParameter(':state', $filter['state']);
        }

        // Exclude given workflow state
        if (isset($filter['stateExclude'])) {
            $queryBuilder->andWhere('CR.state <> :stateExclude');
            $queryBuilder->setParameter(':stateExclude', $filter['stateExclude']);
        }

        // Find default value for sort field so it can be excluded
        /** @var $sortFieldDefaultValue string */
        $sortFieldName = $this->config->getString('entity.prettyname', NULL);

        if ($sortFieldName) {
            $queryBuilder
                ->leftJoin(
                    'CR.metadata',
                    'MD',
                    Expr\Join::WITH,
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('MD.key', ':metadataKey')
                    )
                )
                ->setParameter(':metadataKey', $sortFieldName);

            if ($sortOrder !== 'DESC') {
                $sortOrder = 'ASC';
            }
            $queryBuilder->orderBy('orderfield', $sortOrder);
        }

        $result = $queryBuilder->getQuery()->execute();

        $this->logger->info("Returning connections");

        return $result;
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
        $dto->setState('testaccepted');
        $dto->setIsActive(true);
        $dto->setAllowAllEntities(true);
        if ($type) {
            $dto->setType($type);
        }

        return $dto;
    }

    /**
     * Creates a new connection and/or revision from a data transfer object.
     *
     * @param ConnectionDto $dto
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @throws \Janus\ServiceRegistry\Entity\Connection\ConnectionExistsException
     */
    public function save(ConnectionDto $dto)
    {
        $entityManager = $this->entityManager;

        $entityManager->getConnection()->beginTransaction();

        $connection = $this->createConnection(
            $dto->getName(),
            $dto->getType(),
            $dto->getId()
        );

        // Create new revision
        $connection->update(
            $this->config,
            $dto->getName(),
            $dto->getType(),
            $dto->getParentRevisionNr(),
            $dto->getRevisionNote(),
            $dto->getState(),
            $dto->getExpirationDate(),
            $dto->getMetadataUrl(),
            $dto->getAllowAllEntities(),
            $dto->getArpAttributes(),
            $dto->getManipulationCode(),
            $dto->getIsActive(),
            $dto->getNotes()
        );

        // Update connection and new revision
        $entityManager->persist($connection);
        $entityManager->flush($connection);

        $latestRevision = $connection->getLatestRevision();
        foreach ($this->disassembleConnectionReferences($dto->getAllowedConnections()) as $referencedConnection) {
            $latestRevision->allowConnection($referencedConnection);
        }

        foreach ($this->disassembleConnectionReferences($dto->getBlockedConnections()) as $referencedConnection) {
            $latestRevision->blockConnection($referencedConnection);
        }

        foreach ($this->disassembleConnectionReferences($dto->getDisableConsentConnections()) as $referencedConnection) {
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
        $flatMetadata = $dto->getMetadata()->flatten();
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

        return $connection;
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
        $this->logger->info("Connection Service: Trying to delete connection '{$id}'");

        try {
            $entityManager = $this->entityManager;

            $entityManager->getConnection()->beginTransaction();

            $entityManager
                ->createQueryBuilder()
                ->delete()
                ->from('Janus\ServiceRegistry\Entity\Connection', 'c')
                ->where('c.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();

            $subscriptionAddress = 'ENTITYUPDATE-' . $id;
            $entityManager
                ->createQueryBuilder()
                ->delete()
                ->from('Janus\ServiceRegistry\Entity\User\Subscription', 's')
                ->where('s.address = :address')
                ->setParameter('address', $subscriptionAddress)
                ->getQuery()
                ->execute();

            $entityManager->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->logger->error("Connnection Service: Entity or it's subscriptions could not be deleted.");
            throw $ex;
        }

        $this->logger->info("Connection Service: Deleted connection '{$id}'");
    }
}
