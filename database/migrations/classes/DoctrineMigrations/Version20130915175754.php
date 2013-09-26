<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Provision separate list of unique entities, must be done after previous patch since mixing methods and SQL is not possible
 *
 * Class Version20130915175753
 * @package DoctrineMigrations
 */
class Version20130915175754 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    public function up(Schema $schema)
    {
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            // Make sure insertion fails if entity is too long
            $this->addSql("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");
        }

        // Provision the list of entities
        $this->addSql("
            INSERT INTO {$this->tablePrefix}entityId
            SELECT  eid,
                    entityid
            FROM    {$this->tablePrefix}entity AS E
            WHERE   revisionid = (
              SELECT MAX(revisionid)
              FROM  {$this->tablePrefix}entity
              WHERE eid = E.eid
            )
        ");

    }

    public function down(Schema $schema)
    {
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            // Make sure insertion fails if entity is too long
            $this->addSql("SET FOREIGN_KEY_CHECKS = 0");
        }
        $this->addSql("DELETE FROM {$this->tablePrefix}entityId");
    }
}