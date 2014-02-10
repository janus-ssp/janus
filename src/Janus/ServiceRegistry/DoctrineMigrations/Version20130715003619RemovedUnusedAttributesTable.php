<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003619RemovedUnusedAttributesTable
 * @package DoctrineMigrations
 */
class Version20130715003619RemovedUnusedAttributesTable extends AbstractMigration
{
    /**
     * Attributes table was not used in the code anymore so can be removed
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $prefixedTableName = DB_TABLE_PREFIX . 'attribute';
        if ($schema->hasTable($prefixedTableName)) {
            $schema->dropTable($prefixedTableName);
        }
    }

    /**
     * @param Schema $schema
     *
     * Just here to test reverse migration
     */
    public function down(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE " . DB_TABLE_PREFIX . "attribute (
              eid int(11) NOT NULL,
              revisionid int(11) NOT NULL,
              `key` text NOT NULL,
              `value` text NOT NULL,
              created char(25) NOT NULL,
              ip char(39) NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }
}