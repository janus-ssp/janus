<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version20130715003622ConvertBooleanEnumsToChar
 * @package DoctrineMigrations
 */
class Version20130715003622ConvertBooleanEnumsToChar extends JanusMigration
{
    /**
     * Doctrine is not (very) compatible with ENUM fields, so change them to char fields
     * see: http://docs.doctrine-project.org/en/latest/cookbook/mysql-enums.html
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "entity
            CHANGE `active` `active` CHAR(3) NOT NULL DEFAULT 'yes'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "message
            CHANGE `read` `read` CHAR(3) NOT NULL DEFAULT 'no'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "entity
            CHANGE `active` `active` ENUM('yes','no') NOT NULL DEFAULT 'yes'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "message
            CHANGE `read` `read` ENUM('yes','no') DEFAULT 'no'");
    }
}
