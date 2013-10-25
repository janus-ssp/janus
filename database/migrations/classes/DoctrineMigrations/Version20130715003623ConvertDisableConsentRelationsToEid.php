<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version3ConvertDisableConsentRelationsToEid
 * @package DoctrineMigrations
 */
class Version20130715003623ConvertDisableConsentRelationsToEid extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Convert relation based on entityid to eid since this makes renaming an entity possible
     * And makes it possible to create a key containing this column without a length (not portable)
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                ADD COLUMN remoteeid INT(11) NOT NULL;
        ");

        $this->addSql("
            UPDATE {$this->tablePrefix}disableConsent AS DC
            INNER JOIN (
                SELECT  entityid,
                        eid
                FROM    " . $this->tablePrefix . "entity AS E
                WHERE   revisionid = (
                    SELECT  MAX(revisionid) AS revisionid
                    FROM    " . $this->tablePrefix . "entity
                    WHERE   eid = E.eid
                )
            ) AS LATEST_ENTITY_REVISION
                ON  DC.remoteentityid = LATEST_ENTITY_REVISION.entityid
            SET DC.remoteeid = LATEST_ENTITY_REVISION.eid;
        ");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                DROP remoteentityid;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                ADD COLUMN remoteentityid TEXT NOT NULL;
        ");

        $this->addSql("
            UPDATE {$this->tablePrefix}disableConsent AS DC
            INNER JOIN (
                SELECT  entityid,
                        eid
                FROM    " . $this->tablePrefix . "entity AS E
                WHERE   revisionid = (
                    SELECT  MAX(revisionid) AS revisionid
                    FROM    " . $this->tablePrefix . "entity
                    WHERE   eid = E.eid
                )
            ) AS LATEST_ENTITY_REVISION
                ON  DC.remoteeid = LATEST_ENTITY_REVISION.eid
            SET DC.remoteentityid = LATEST_ENTITY_REVISION.entityid;
        ");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                DROP remoteeid;
        ");
    }
}
