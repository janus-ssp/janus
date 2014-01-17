<?php
namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003621RemoveAclObsoleteIndexes
 * @package DoctrineMigrations
 */
class Version20130715003621RemoveAclObsoleteIndexes extends AbstractMigration
{
    /**
     * Remove obsolete remoteeid indexes which are no longer used but were in the janus.sql for a while but was never
     * part of any upgrade instructions and was never useful
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->removeObsoleteIndex($schema, 'allowedEntity');
        $this->removeObsoleteIndex($schema, 'blockedEntity');
    }

    /**
     * @param Schema $schema
     * @param string $tableName
     */
    private function removeObsoleteIndex(Schema $schema, $tableName)
    {
        $prefixedTableName = DB_TABLE_PREFIX . $tableName;
        if ($schema->getTable($prefixedTableName)->hasIndex('remoteeid')) {
            $this->addSql("
            DROP INDEX remoteeid
              ON {$prefixedTableName}");
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //
    }
}