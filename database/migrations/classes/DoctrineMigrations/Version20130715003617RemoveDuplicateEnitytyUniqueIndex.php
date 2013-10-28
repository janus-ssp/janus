<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003617RemoveDuplicateEnitytyUniqueIndex extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Remove a unique index which has the same columns as: 'janus__entity__eid_revisionid'
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Surfnet patch 00014 / BACKLOG-675: Add manipulation field to entity
        $prefixedTableName = $this->tablePrefix . 'entity';
        $table = $schema->getTable($prefixedTableName);

        if ($table->hasIndex('eid')) {
            $this->addSql("
                DROP INDEX `eid` ON {$prefixedTableName}
            ");
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE {$this->tablePrefix}entity
                ADD UNIQUE KEY eid(eid,revisionid)
        ");
    }
}
