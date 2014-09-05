<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;

class Version20130715003617CorrectMetadataKeyColumn extends JanusMigration
{
    /**
     * @param Schema $schema
     *
     * Convert Doctrine cannot handle a key which is too long
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "metadata
              CHANGE `key` `key` varchar(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "metadata
              CHANGE `key` `key` text NOT NULL
        ");
    }
}