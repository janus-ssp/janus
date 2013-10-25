<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * Class Version20130915175753
 * @package DoctrineMigrations
 */
class Version20130915175753AddUniqueEntitiesTable extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    public function up(Schema $schema)
    {
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");

        // Create table for unique entities
        $this->addSql("
            CREATE TABLE {$this->tablePrefix}entityId (
                eid INT AUTO_INCREMENT NOT NULL,
                entityid VARCHAR(255) NOT NULL,
                UNIQUE INDEX entityid (entityid),
                PRIMARY KEY(eid)
            )
            DEFAULT CHARACTER SET utf8
            COLLATE utf8_unicode_ci
            ENGINE = InnoDB");

        // Provision the list of entities
        $this->addSql("
            INSERT INTO {$this->tablePrefix}entityId
            SELECT  eid,
                    entityid
            FROM    {$this->tablePrefix}entity AS E
            WHERE   revisionid = (
              SELECT MAX(revisionid)
              FROM  {$this->tablePrefix}entity
              WHERE eid = E.eid
            )
        ");

        // Make sure insertion fails if entity is too long
        $this->addSql("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

        // Add references to unique entities
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}allowedEntity
                ADD CONSTRAINT FK_B71F875B3C2FCD2 FOREIGN KEY (remoteeid) REFERENCES {$this->tablePrefix}entityId (eid)");
        $this->addSql("
            CREATE INDEX IDX_B71F875B3C2FCD2
                ON {$this->tablePrefix}allowedEntity (remoteeid)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}blockedEntity
                ADD CONSTRAINT FK_C3FFDC7F3C2FCD2 FOREIGN KEY (remoteeid) REFERENCES {$this->tablePrefix}entityId (eid)");

        $this->addSql("
            CREATE INDEX IDX_C3FFDC7F3C2FCD2
                ON {$this->tablePrefix}blockedEntity (remoteeid)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
            ADD CONSTRAINT FK_C88326593C2FCD2 FOREIGN KEY (remoteeid) REFERENCES {$this->tablePrefix}entityId (eid)");
        $this->addSql("
            CREATE INDEX IDX_C88326593C2FCD2
                ON {$this->tablePrefix}disableConsent (remoteeid)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}entity
                ADD CONSTRAINT FK_B5B24B904FBDA576 FOREIGN KEY (eid) REFERENCES {$this->tablePrefix}entityId (eid)");
        $this->addSql("
            CREATE INDEX IDX_B5B24B904FBDA576
                ON {$this->tablePrefix}entity (eid)");
    }

    public function down(Schema $schema)
    {
        // Remove foreign keys
        $this->addSql("ALTER TABLE janus__allowedEntity DROP FOREIGN KEY FK_B71F875B3C2FCD2");
        $this->addSql("DROP INDEX IDX_B71F875B3C2FCD2 ON janus__allowedEntity");

        $this->addSql("ALTER TABLE janus__blockedEntity DROP FOREIGN KEY FK_C3FFDC7F3C2FCD2");
        $this->addSql("DROP INDEX IDX_C3FFDC7F3C2FCD2 ON janus__blockedEntity");

        $this->addSql("ALTER TABLE janus__disableConsent DROP FOREIGN KEY FK_C88326593C2FCD2");
        $this->addSql("DROP INDEX IDX_C88326593C2FCD2 ON janus__disableConsent");

        $this->addSql("ALTER TABLE janus__entity DROP FOREIGN KEY FK_B5B24B904FBDA576");
        $this->addSql("DROP INDEX IDX_B5B24B904FBDA576 ON janus__entity");

        // Remove table
        $this->addSql("DROP TABLE janus__entityId");
    }
}