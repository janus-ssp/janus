<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * @package DoctrineMigrations
 */
class Version20130715003623RenameEntityToConnection extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "entity TO " . DB_TABLE_PREFIX . "connection");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "hasEntity TO " . DB_TABLE_PREFIX . "hasConnection");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "allowedEntity TO " . DB_TABLE_PREFIX . "allowedConnection");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "blockedEntity TO " . DB_TABLE_PREFIX . "blockedConnection");
    }

    public function down(Schema $schema)
    {
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "connection TO " . DB_TABLE_PREFIX . "entity");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "hasConnection TO " . DB_TABLE_PREFIX . "hasEntity");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "allowedConnection TO " . DB_TABLE_PREFIX . "allowedEntity");
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "blockedConnection TO " . DB_TABLE_PREFIX . "blockedEntity");
    }
}