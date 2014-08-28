<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * @package DoctrineMigrations
 */
class Version20130715003625SplitConnectionToConnectionAndConnectionRevision extends JanusMigration
{
    public function up(Schema $schema)
    {
        // Rename entities table
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "connection TO " . $this->getTablePrefix() . "connectionRevision");

        // Create table for unique entities
        $this->addSql("
            CREATE TABLE " . $this->getTablePrefix() . "connection (
                id INT AUTO_INCREMENT NOT NULL,
                revisionNr INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                type varchar(50) NOT NULL,
                user int(11) DEFAULT NULL,
                created char(25) DEFAULT NULL,
                ip char(39) DEFAULT NULL,

                UNIQUE INDEX unique_name_per_type (name, type),
                PRIMARY KEY(id),
                KEY `revisionNr` (`revisionNr`)
            )
            DEFAULT CHARACTER SET utf8
            COLLATE utf8_unicode_ci
            ENGINE = InnoDB");

        // Make sure insertion fails if name is too long
        $this->addSql("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

        // Provision the list of entities
        $this->addSql("
            INSERT INTO " . $this->getTablePrefix() . "connection
            SELECT  eid,
                    revisionid,
                    entityid,
                    type,
                    user,
                    created,
                    ip
            FROM    " . $this->getTablePrefix() . "connectionRevision AS CR
            WHERE   revisionid = (
              SELECT MAX(revisionid)
              FROM  " . $this->getTablePrefix() . "connectionRevision
              WHERE eid = CR.eid
            )
        ");
    }

    public function down(Schema $schema)
    {
        // Remove table
        $this->addSql("DROP TABLE " . $this->getTablePrefix() . "connection");
        
        // Rename entities table
        $this->addSql("RENAME TABLE " . $this->getTablePrefix() . "connectionRevision TO " . $this->getTablePrefix() . "connection");
    }
}