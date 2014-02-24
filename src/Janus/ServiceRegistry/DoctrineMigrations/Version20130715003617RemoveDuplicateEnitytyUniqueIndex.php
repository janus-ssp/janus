<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003617RemoveDuplicateEnitytyUniqueIndex extends JanusMigration
{
    /**
     * Remove a unique index which has the same columns as: 'janus__entity__eid_revisionid'
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
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "entity
                ADD UNIQUE KEY eid(eid,revisionid)
        ");
    }
}
