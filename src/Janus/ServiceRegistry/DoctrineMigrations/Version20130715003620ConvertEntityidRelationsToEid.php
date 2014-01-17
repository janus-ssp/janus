<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003620ConvertEntityidRelationsToEid
 * @package DoctrineMigrations
 */
class Version20130715003620ConvertEntityidRelationsToEid extends AbstractMigration
{
    /**
     * Convert relation based on entityid to eid since this makes renaming an entity possible
     * And makes it possible to create a key containing this column without a length (not portable)
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->convertFromEntityIdToEid($schema, 'allowedEntity');
        $this->convertFromEntityIdToEid($schema, 'blockedEntity');
        $this->convertFromEntityIdToEid($schema, 'disableConsent');
    }

    /**
     * Since this patch was already mentioned in manual upgrade instructions some checkes to see it the upgrade was performed yet are necessary.
     *
     * @param Schema $schema
     * @param string $tableName
     */
    private function convertFromEntityIdToEid(Schema $schema, $tableName)
    {
        $prefixedTableName = DB_TABLE_PREFIX . $tableName;
        $table = $schema->getTable($prefixedTableName);

        if (!$table->hasColumn('remoteeid')) {
            $this->addSql("
                ALTER TABLE {$prefixedTableName}
                    ADD COLUMN remoteeid INT(11) NOT NULL AFTER revisionid;
            ");

            $this->addSql("
                UPDATE {$prefixedTableName} AS RELATION
                INNER JOIN (
                    SELECT  entityid,
                            eid
                    FROM    " . DB_TABLE_PREFIX . "entity AS E
                    WHERE   revisionid = (
                        SELECT  MAX(revisionid) AS revisionid
                        FROM    " . DB_TABLE_PREFIX . "entity
                        WHERE   eid = E.eid
                    )
                ) AS LATEST_ENTITY_REVISION
                    ON  RELATION.remoteentityid = LATEST_ENTITY_REVISION.entityid
                SET RELATION.remoteeid = LATEST_ENTITY_REVISION.eid;
            ");
        }

        if ($table->hasColumn('remoteentityid')) {
            $this->addSql("
                ALTER TABLE {$prefixedTableName}
                    DROP remoteentityid;
            ");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->convertFromEidToEntityId('allowedEntity');
        $this->convertFromEidToEntityId('blockedEntity');
        $this->convertFromEidToEntityId('disableConsent');
    }

    /**
     * @param string $tableName
     */
    private function convertFromEidToEntityId($tableName)
    {
        $prefixedTableName = DB_TABLE_PREFIX . $tableName;

        $this->addSql("
            ALTER TABLE $prefixedTableName
                ADD COLUMN remoteentityid TEXT NOT NULL AFTER revisionid;
        ");

        $this->addSql("
            UPDATE $prefixedTableName AS RELATION
            INNER JOIN (
                SELECT  entityid,
                        eid
                FROM    " . DB_TABLE_PREFIX . "entity AS E
                WHERE   revisionid = (
                    SELECT  MAX(revisionid) AS revisionid
                    FROM    " . DB_TABLE_PREFIX . "entity
                    WHERE   eid = E.eid
                )
            ) AS LATEST_ENTITY_REVISION
                ON  RELATION.remoteeid = LATEST_ENTITY_REVISION.eid
            SET RELATION.remoteentityid = LATEST_ENTITY_REVISION.entityid;
        ");

        $this->addSql("
            ALTER TABLE $prefixedTableName
                DROP remoteeid;
        ");
    }
}