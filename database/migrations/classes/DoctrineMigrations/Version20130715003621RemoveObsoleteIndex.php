<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003621RemoveObsoleteIndex
 * @package DoctrineMigrations
 */
class Version20130715003621RemoveObsoleteIndex extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Remove obsolete remoteeid indexes which are no longer used
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            DROP INDEX remoteeid
                ON janus__allowedEntity");
        $this->addSql("
            DROP INDEX remoteeid
                ON janus__blockedEntity");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("
          CREATE INDEX remoteeid
              ON janus__allowedEntity (remoteeid)");
        $this->addSql("
          CREATE INDEX remoteeid
              ON janus__blockedEntity (remoteeid)");
    }
}