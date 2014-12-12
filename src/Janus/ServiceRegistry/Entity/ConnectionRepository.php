<?php

namespace Janus\ServiceRegistry\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Janus\ServiceRegistry\Entity\Connection\Revision;
use Janus\ServiceRegistry\Command\FindConnectionRevisionCommand;
use Monolog\Logger;

class ConnectionRepository extends EntityRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EntityManager $entityManager
     * @param Logger $logger
     */
    public function __construct(EntityManager $entityManager, Logger $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Finds the latest revision number for a given connection id
     *
     * @param FindConnectionRevisionCommand $command
     * @return int|null
     */
    public function findLatestRevisionNr(FindConnectionRevisionCommand $command)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('C.revisionNr ')
            ->from('Janus\ServiceRegistry\Entity\Connection', 'C')
            ->where('C.id = :id')
            ->setParameter(':id', $command->id);

        if(!is_null($command->state)) {
            $queryBuilder
                ->innerJoin('Janus\ServiceRegistry\Entity\ConnectionRevision', 'CR')
                ->andWhere('CR.state = :state')
                ->setParameter(':state', $command->state);
        }

        $revisionNr = $queryBuilder->getQuery()->getSingleScalarResult();
        if (!is_numeric($revisionNr)) {
            return null;
        }

        return (int) $revisionNr;
    }

    /**
     * Find the latest revisions of all connections that match the given filters.
     *
     * @param array $filter
     * @param null $sortBy
     * @param string $sortOrder
     * @param string $sortFieldName
     * @return Revision[]
     */
    public function findLatestRevisionsWithFilters(
        array $filter = array(),
        $sortBy = null,
        $sortOrder = 'DESC',
        $sortFieldName
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

        if (isset($filter['name'])) {
            $nameFilter = str_replace('*', '%', $filter['name']);
            $queryBuilder
                ->andWhere('C.name LIKE :name')
                ->setParameter('name', $nameFilter);
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
     * Deletes a connection and all of it's relations by id
     *
     * @param int $id
     * @throws \Exception
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