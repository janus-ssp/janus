<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a notes column to the entity revision
 */
class Version20131125111444AddNotesColumnToEntityRevision extends JanusMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `" . $this->getTablePrefix() . "connectionRevision`
              ADD `notes` text
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
