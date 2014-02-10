<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;

class Version20130715123302CorrectArpAttributesColumn extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * Add Comment for doctrine to mark a field containing serialized data
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "arp
              CHANGE `attributes` `attributes` text COMMENT '(DC2Type:array)'
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "arp
              CHANGE `attributes` `attributes` text
        ");
    }
}