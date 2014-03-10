<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003618AddManipulationColumn
 * @package DoctrineMigrations
 */
class Version20130715003618AddManipulationColumn extends JanusMigration
{
    /**
     * Surfnet patch 00014
     * BACKLOG-675: Add manipulation field to entity
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefixedTableName = $this->getTablePrefix() . 'entity';
        $table = $schema->getTable($prefixedTableName);

        if (!$table->hasColumn('manipulation')) {
            $this->addSql("
                ALTER TABLE `{$prefixedTableName}`
                  ADD COLUMN `manipulation` MEDIUMTEXT NULL DEFAULT NULL  AFTER `arp` ;
            ");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $prefixedTableName = $this->getTablePrefix() . 'entity';
        $table = $schema->getTable($prefixedTableName);
        $table->dropColumn('manipulation');
    }
}