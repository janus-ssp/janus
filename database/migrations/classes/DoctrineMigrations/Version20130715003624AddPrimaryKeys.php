<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003624AddPrimaryKeys extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Adds Primary key to each column (doctrine requires this)
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefixedTableName = $this->tablePrefix . 'entity';

        // Since eid is actually a foreign key it cannot be null
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}entity
                CHANGE `revisionid` `revisionid` INT(11) NOT NULL
        ");

        $table = $schema->getTable($prefixedTableName);
        if (!$table->hasPrimaryKey()) {
            $this->addSql("
                ALTER TABLE {$prefixedTableName}
                    ADD PRIMARY KEY (eid, revisionid)
            ");
        }

        if ($table->hasIndex('janus__entity__eid_revisionid')) {
            $this->addSql("
                DROP INDEX `janus__entity__eid_revisionid` ON {$prefixedTableName}
            ");
        }

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}allowedEntity
                ADD PRIMARY KEY (eid, revisionid, remoteeid)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}blockedEntity
                ADD PRIMARY KEY (eid, revisionid, remoteeid)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                ADD PRIMARY KEY (eid, revisionid, remoteeid)");

        // Since eid is actually a foreign key it cannot be null
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}hasEntity
                CHANGE `eid` `eid` INT(11) NOT NULL,
                ADD PRIMARY KEY (uid, eid)");

        // Key does not have to be a text value, this is way too long is cannot be used in keys
        // Also key cannot be null
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}metadata
                CHANGE `key` `key` VARCHAR(255) NOT NULL,
                DROP INDEX `janus__metadata__eid_revisionid_key`,
                ADD PRIMARY KEY (eid, revisionid, `key`)");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}userData
                DROP INDEX uid,
                ADD PRIMARY KEY (uid, `key`)");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}entity
                DROP PRIMARY KEY,
                ADD UNIQUE KEY janus__entity__eid_revisionid (eid,revisionid),
                CHANGE `revisionid` `revisionid` INT(11) DEFAULT NULL
                ");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}allowedEntity
                DROP PRIMARY KEY");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}blockedEntity
                DROP PRIMARY KEY");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}disableConsent
                DROP PRIMARY KEY");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}hasEntity
                DROP PRIMARY KEY,
                CHANGE eid eid int(11) DEFAULT NULL");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}metadata
                DROP PRIMARY KEY,
                CHANGE `key` `key` TEXT NOT NULL,
                ADD UNIQUE INDEX {$this->tablePrefix}metadata__eid_revisionid_key (eid,revisionid,`key`(50))");

        $this->addSql("
            ALTER TABLE {$this->tablePrefix}userData
                DROP PRIMARY KEY,
                ADD UNIQUE INDEX uid (uid, `key`)");
    }
}
