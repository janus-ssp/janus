<?php
namespace Janus\ServiceRegistry\Service;

use Exception;
use PDOException;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\DBAL\DBALException;

use SimpleSAML_Configuration;
use SimpleSAML_Logger;

use sspmod_janus_Database;

use Janus\ServiceRegistry\Entity\Connection;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Entity\Connection\Revision\Metadata;
use Janus\ServiceRegistry\Entity\User;
use Janus\ServiceRegistry\Entity\User\ConnectionRelation;
use Janus\ServiceRegistry\Entity\Connection\ConnectionExistsException;
use Janus\ServiceRegistry\Connection\Dto;

/**
 * Service layer for all kinds of connection related logic
 *
 * Class Janus\ServiceRegistry\Service\ConnectionService
 */
class ConnectionService extends sspmod_janus_Database
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
     * @param EntityManager $entityManager
     * @param SimpleSAML_Configuration $config
     */
    public function __construct(EntityManager $entityManager, SimpleSAML_Configuration $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        parent::__construct($config->getValue('store'));
    }

    /**
     * @param int $id
     * @return Connection
     * @throws Exception
     */
    public function getById($id)
    {
        $connection = $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\Connection')->find($id);
        if (!$connection instanceof Connection) {
            throw new \Exception("Connection '{$id}' not found");
        }

        return $connection;
    }

    /**
     * @param int $eid
     * @param int $revisionNr
     * @return Revision|null
     */
    public function getRevisionByEidAndRevision($eid, $revisionNr = null)
    {
        if ($revisionNr === null || $revisionNr < 0) {
            return  $this->getLatestRevision($eid);
        }

        $connectionRevision = $this
            ->entityManager
            ->getRepository('Janus\ServiceRegistry\Entity\Connection\Revision')
            ->findOneBy(array(
                'connection' => $eid,
                'revisionNr' => $revisionNr
            )
        );

        return $connectionRevision;
    }

    /**
     * Loads a connection by given id
     *
     * @param int $id
     * @return Revision|null
     */
    public function getLatestRevision($id)
    {
        // @todo see if this is the best place to catch the exception.
        try {
            return \sspmod_janus_DiContainer::getInstance()
                ->getEntityManager()
                ->getRepository('Janus\ServiceRegistry\Entity\Connection\Revision')
                ->getLatest($id);
        } catch (NoResultException $ex) {
            return null;
        }
    }

    public function getAllRevisionsByEid($eid)
    {
        return $this->entityManager->getRepository('Janus\ServiceRegistry\Entity\Connection\Revision')->findBy(array(
                'connection' => $eid
            ), array('revisionNr' => 'DESC')
        );
    }

    /**
     * Grants a user permission to a given entity
     *
     * @param Connection  $connection
     * @param User $user
     */
    public function addUserPermission(Connection $connection, User $user)
    {
        $userConnectionRelation = new ConnectionRelation(
            $user,
            $connection
        );

        $this->entityManager->persist($userConnectionRelation);
        $this->entityManager->flush();
    }

    /**
     * @param array $filter
     * @param string $sortBy
     * @param string $sortOrder
     * @internal param string $state
     * @internal param string $stateExclude
     * @internal param bool $allowedUserId
     * @return mixed
     */
    public function load(
        array $filter = array(),
        $sortBy = null,
        $sortOrder = 'DESC'
    )
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

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
                    $queryBuilder->expr()->like('UCR.user', ':userId')
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

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * Creates a new connection and/or revision from a data transfer object.
     *
     * @param Dto $dto
     *
     * @return Connection
     * @throws \Janus\ServiceRegistry\Entity\Connection\ConnectionExistsException
     */
    public function createFromDto(Dto $dto)
    {
        $entityManager = $this->getEntityManager();

        $entityManager->beginTransaction();

        $connection = $this->createConnection(
            $entityManager,
            $dto->getName(),
            $dto->getType(),
            $dto->getId()
        );

        // Create new revision
        $connection->update(
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
        try {
            $entityManager->flush();
        } catch (DBALException $ex) {
            $pdoException = $ex->getPrevious();
            if ($pdoException instanceof PDOException) {
                if ($pdoException->getCode() == 23000) {
                    throw new ConnectionExistsException($pdoException->getMessage());
                }
            }
        }

        // Store metadata
        $flatMetadata = $dto->getMetadata()->flatten();
        $latestRevision = $connection->getLatestRevision();
        foreach ($flatMetadata as $key => $value) {
            // Note that empty values are no longer saved
            if ($value === null || $value === '') {
                break;
            }
            $metadataRecord = new Metadata(
                $latestRevision,
                $key,
                $value
            );
            $entityManager->persist($metadataRecord);
        }
        $entityManager->flush();

        $entityManager->commit();

        return $connection;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = array();
    }

    /**
     * @param EntityManager $entityManager
     * @param string $name
     * @param string $type
     * @param int $id
     * @return Connection
     */
    private function createConnection(
        EntityManager $entityManager,
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

        return $connection = $this->getById($id);
    }

    /**
     * Deletes a connection and all of it's relations by id
     *
     * @param int $id
     * @throws Exception
     */
    public function deleteById($id)
    {
        try {
            $entityManager = $this->getEntityManager();

            $entityManager->beginTransaction();

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

            $entityManager->commit();
        } catch (\Exception $ex) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Entity or it\'s subscriptions could not be deleted.'
            );

            throw $ex;
        }
    }
}
