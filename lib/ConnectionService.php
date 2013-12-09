<?php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

/**
 * Service layer for all kinds of connection related logic
 *
 * Class sspmod_janus_ConnectionService
 */
class sspmod_janus_ConnectionService extends sspmod_janus_Database
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
     * @return sspmod_janus_Model_Connection
     * @throws Exception
     */
    public function getById($id)
    {
        $connection = $this->entityManager->getRepository('sspmod_janus_Model_Connection')->find($id);
        if (!$connection instanceof sspmod_janus_Model_Connection) {
            throw new \Exception("Connection '{$id}' not found");
        }

        return $connection;
    }

    public function getRevisionByEidAndRevision($eid, $revisionNr = null)
    {
        if ($revisionNr === null || $revisionNr < 0) {
            $revisionNr = $this->getLatestRevision($eid);
        }

        $connectionRevision = $this->entityManager->getRepository('sspmod_janus_Model_Connection_Revision')->findOneBy(array(
                'connection' => $eid,
                'revisionNr' => $revisionNr
            )
        );
        return $connectionRevision;
    }

    public function getLatestRevision($eid)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        return $queryBuilder
            ->select('MAX(r.revisionNr) as maxRev')
            ->from('sspmod_janus_Model_Connection_Revision', 'r')
            ->where($queryBuilder->expr()->eq('r.connection', ':eid'))
            ->setParameter('eid', $eid)
            ->getQuery()->getSingleScalarResult();
    }

    public function getAllRevisionsByEid($eid)
    {
        return $this->entityManager->getRepository('sspmod_janus_Model_Connection_Revision')->findBy(array(
                'connection' => $eid
            ), array('revisionNr' => 'DESC')
        );
    }

    /**
     * Grants a user permission to a given entity
     *
     * @param sspmod_janus_Model_Connection $connection
     * @param sspmod_janus_Model_User $user
     */
    public function addUserPermission(sspmod_janus_Model_Connection $connection, sspmod_janus_Model_User $user)
    {
        $userConnectionRelation = new sspmod_janus_Model_User_ConnectionRelation(
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
            ->from('sspmod_janus_Model_Connection_Revision', 'CR')
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
     * @param sspmod_janus_Model_Connection_Revision_Dto $dto
     *
     * @return sspmod_janus_Model_Connection
     */
    public function createFromDto(sspmod_janus_Model_Connection_Revision_Dto $dto)
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
        $entityManager->flush();
        $entityManager->commit();

        return $connection;
    }

    /**
     * @param EntityManager $entityManager
     * @param string $name
     * @param string $type
     * @param int $id
     * @return sspmod_janus_Model_Connection
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
            $connection = new sspmod_janus_Model_Connection($name, $type);
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
                ->from('sspmod_janus_Model_Connection', 'c')
                ->where('c.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();

            $subscriptionAddress = 'ENTITYUPDATE-'.$id;
            $entityManager
                ->createQueryBuilder()
                ->delete()
                ->from('sspmod_janus_Model_User_Subscription', 's')
                ->where('s.address = :address')
                ->setParameter('address', $subscriptionAddress)
                ->getQuery()
                ->execute();

            $entityManager->commit();
        } catch(\Exception $ex) {
            SimpleSAML_Logger::error(
                'JANUS:deleteEntity - Entity or it\'s subscriptions could not be deleted.'
            );

            throw $ex;
        }
    }
}
