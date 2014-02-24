<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * @package DoctrineMigrations
 */
class Version20130715003623RenameEntityToConnection extends JanusMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "entity TO " . $this->getTablePrefix() . "connection");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "hasEntity TO " . $this->getTablePrefix() . "hasConnection");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "allowedEntity TO " . $this->getTablePrefix() . "allowedConnection");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "blockedEntity TO " . $this->getTablePrefix() . "blockedConnection");
    }

    public function down(Schema $schema)
    {
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "connection TO " . $this->getTablePrefix() . "entity");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "hasConnection TO " . $this->getTablePrefix() . "hasEntity");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "allowedConnection TO " . $this->getTablePrefix() . "allowedEntity");
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "blockedConnection TO " . $this->getTablePrefix() . "blockedEntity");
    }
}