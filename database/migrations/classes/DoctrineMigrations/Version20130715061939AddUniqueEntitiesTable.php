<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * Class Version20130715061939AddUniqueEntitiesTable
 * @package DoctrineMigrations
 */
class Version20130715061939AddUniqueEntitiesTable extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // Rename entities table
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "entity TO " . DB_TABLE_PREFIX . "entityRevision");

        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");

        // Create table for unique entities
        $this->addSql("
            CREATE TABLE " . DB_TABLE_PREFIX . "entity (
                eid INT AUTO_INCREMENT NOT NULL,
                entityid VARCHAR(255) NOT NULL,
                UNIQUE INDEX entityid (entityid),
                PRIMARY KEY(eid)
            )
            DEFAULT CHARACTER SET utf8
            COLLATE utf8_unicode_ci
            ENGINE = InnoDB");

        // Make sure insertion fails if entity is too long
        $this->addSql("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

        // Provision the list of entities
        $this->addSql("
            INSERT INTO " . DB_TABLE_PREFIX . "entity
            SELECT  eid,
                    entityid
            FROM    " . DB_TABLE_PREFIX . "entityRevision AS EV
            WHERE   revisionid = (
              SELECT MAX(revisionid)
              FROM  " . DB_TABLE_PREFIX . "entity
              WHERE eid = EV.eid
            )
        ");
    }

    public function down(Schema $schema)
    {
        // Remove table
        $this->addSql("DROP TABLE " . DB_TABLE_PREFIX . "entity");
        
        // Rename entities table
        $this->addSql("RENAME TABLE " . DB_TABLE_PREFIX . "entityRevision TO " . DB_TABLE_PREFIX . "entity");
    }
}