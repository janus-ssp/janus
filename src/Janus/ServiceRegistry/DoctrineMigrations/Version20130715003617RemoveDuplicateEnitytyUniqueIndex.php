<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003617RemoveDuplicateEnitytyUniqueIndex extends JanusMigration
{
    /**
     * Removes several unique keys and primary keys which either served the same purposes or will
     * cause trouble later on. This is necessary to support updating from janus 1.12, 1.13 and SURFnet serviceregistry
     *
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefixedTableName = $this->getTablePrefix() . 'entity';
        $table = $schema->getTable($prefixedTableName);

        if ($table->hasIndex('eid')) {
            $this->addSql("
                DROP INDEX `eid` ON {$prefixedTableName}
            ");
        }

        if ($table->hasIndex('janus__entity__eid_revisionid')) {
            $this->addSql("
                DROP INDEX `janus__entity__eid_revisionid` ON {$prefixedTableName}
            ");
        }

        // Remove primary key that was introduced in janus 1.13
        if ($table->hasPrimaryKey()) {
            $this->addSql("ALTER TABLE " . $prefixedTableName . "
                DROP PRIMARY KEY");
        }

        $table->addUniqueIndex(array('eid', 'revisionid'), 'unique_revision');
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "entity
                DROP INDEX unique_revision,
                ADD UNIQUE KEY eid(eid,revisionid),
                ADD UNIQUE KEY janus__entity__eid_revisionid(eid,revisionid)
        ");
    }
}
