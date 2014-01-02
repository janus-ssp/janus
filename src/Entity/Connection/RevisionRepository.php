<?php

namespace Janus\Entity\Connection;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use Janus\Entity\Connection\Revision;

class RevisionRepository extends EntityRepository
{
    /**
     * Loads the latest revision of a Connection
     *
     * @param int $connectionId
     * @return Revision|null
     */
    public function getLatest($connectionId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        return $queryBuilder
            ->select('CR')
            ->from('Janus\Entity\Connection\Revision','CR')
            // Filter latest revision
            ->innerJoin(
                'CR.connection',
                'C',
                Expr\Join::WITH,
                'C.revisionNr = CR.revisionNr'
            )
            ->where('CR.connection = :connectionId')
            ->setParameter(':connectionId', $connectionId)
            ->getQuery()
            ->getSingleResult();
    }
}

