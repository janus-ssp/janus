<?php

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * Class sspmod_Janus_Repository_Connection_RevisionRepository
 */
class sspmod_Janus_Repository_Connection_RevisionRepository extends EntityRepository
{
    /**
     * Loads the latest revision of a Connection
     *
     * @param int $connectionId
     * @return sspmod_janus_Model_Connection_Revision|null
     */
    public function getLatest($connectionId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        return $queryBuilder
            ->select('CR')
            ->from('sspmod_janus_Model_Connection_Revision','CR')
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

