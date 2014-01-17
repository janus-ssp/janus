<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;

class Version20130715003617CorrectMetadataKeyColumn extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * Convert Doctrine cannot handle a key which is too long
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "metadata
              CHANGE `key` `key` varchar(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "metadata
              CHANGE `key` `key` text NOT NULL
        ");
    }
}