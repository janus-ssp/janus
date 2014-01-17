<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003617RemoveDuplicateEnitytyUniqueIndex extends AbstractMigration
{
    /**
     * Remove a unique index which has the same columns as: 'janus__entity__eid_revisionid'
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefixedTableName = DB_TABLE_PREFIX . 'entity';
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
            ALTER TABLE " . DB_TABLE_PREFIX . "entity
                ADD UNIQUE KEY eid(eid,revisionid)
        ");
    }
}
