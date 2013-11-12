<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class Version20130715003623ConvertCompositeRelationsToEntityRevisionToSingle
 * @package DoctrineMigrations
 */
class Version20130715003623ConvertCompositeRelationsToEntityRevisionToSingle extends AbstractMigration
{
    /**
     * Add an autoincrementing id to to entity which can be used to refer instead of the composite eid/revisionid
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Since eid is actually a foreign key it cannot be null
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");

        $entityTableName = DB_TABLE_PREFIX . 'entity';
        $this->addSql("
            ALTER TABLE {$entityTableName}
                CHANGE `revisionid` `revisionid` INT(11) NOT NULL
        ");

        // Remove possible primary key (like the one added by surfnet patch 5)
        if ($schema->getTable($entityTableName)->hasPrimaryKey()) {
            $this->addSql("
                ALTER TABLE {$entityTableName}
                    DROP PRIMARY KEY
            ");
        }

        // Add new autoincrement coljumn and mark it as primary key
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . "entity
            ADD id INT PRIMARY KEY AUTO_INCREMENT FIRST");

        // Convert all tables to use the new column
        $this->addSql("DELETE FROM " . DB_TABLE_PREFIX . "allowedEntity WHERE eid = 0 OR remoteeid = 0");
        $this->convertCompositeRelationsToSingle('allowedEntity', array('remoteeid' => 'remoteeid'));
        $this->addSql("DELETE FROM " . DB_TABLE_PREFIX . "blockedEntity WHERE eid = 0 OR remoteeid = 0");
        $this->convertCompositeRelationsToSingle('blockedEntity', array('remoteeid' => 'remoteeid'));
        $this->addSql("DELETE FROM " . DB_TABLE_PREFIX . "disableConsent WHERE eid = 0 OR remoteeid = 0");
        $this->convertCompositeRelationsToSingle('disableConsent', array('remoteeid' => 'remoteeid'));

        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX . "metadata
            DROP KEY janus__metadata__eid_revisionid_key");
        $this->convertCompositeRelationsToSingle('metadata', array('key' => '`key`(50)'));
    }

    /**
     * @param string $name
     * @param array $primaryKeyFields
     */
    private function convertCompositeRelationsToSingle($name, array $primaryKeyFields)
    {
        // Add new column for the single relationship
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            ADD entityRevisionId INT(11) NOT NULL FIRST");

        // Provision new column
        $this->addSql("
            UPDATE  " . DB_TABLE_PREFIX  . $name . " AS RELATION
            INNER JOIN " . DB_TABLE_PREFIX  . "entity AS ENTITY_REVISION
                ON RELATION.eid = ENTITY_REVISION.eid
                AND RELATION.revisionid = ENTITY_REVISION.revisionid
            SET RELATION.entityRevisionId = ENTITY_REVISION.id
        ");

        $this->addSql("DELETE FROM " . DB_TABLE_PREFIX . "{$name} WHERE entityRevisionId = 0");

        // Build a list of primary key fields
        $primaryKeyFieldsDefault = array('entityRevisionId' => 'entityRevisionId');
        $primaryKeyFieldsTotal = array_merge($primaryKeyFieldsDefault, $primaryKeyFields);

        // Add a primary key including the new entity revision id column
        $primaryKeyFieldsCsv = implode(',', $primaryKeyFieldsTotal);

        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            ADD PRIMARY KEY ({$primaryKeyFieldsCsv})");

        // Remove obsolete columns
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            DROP eid,
            DROP revisionid");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . "
            DROP id");

        // Convert all tables to use the new column
        $this->convertSingleRelationsToComposite('allowedEntity');
        $this->convertSingleRelationsToComposite('blockedEntity');
        $this->convertSingleRelationsToComposite('disableConsent');
        $this->convertSingleRelationsToComposite('metadata');

        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "metadata
                ADD UNIQUE KEY janus__metadata__eid_revisionid_key (eid,revisionid,`key`(50))
        ");

        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "entity
                DROP PRIMARY KEY,
                ADD UNIQUE KEY janus__entity__eid_revisionid (eid,revisionid),
                CHANGE `revisionid` `revisionid` INT(11) DEFAULT NULL
        ");
    }

    /**
     * @param string $name
     */
    private function convertSingleRelationsToComposite($name)
    {
        // Add old columns
        // @todo fix order
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            ADD eid int(11) NOT NULL AFTER entityRevisionId,
            ADD revisionid int(11) NOT NULL AFTER eid");


        // Provision olds columns
        $this->addSql("
            UPDATE  " . DB_TABLE_PREFIX  . $name . " AS RELATION
            INNER JOIN " . DB_TABLE_PREFIX  . "entity AS ENTITY_REVISION
                ON RELATION.entityRevisionId = ENTITY_REVISION.id
            SET RELATION.eid = ENTITY_REVISION.eid,
                RELATION.revisionid = ENTITY_REVISION.revisionid
        ");

        // Add a primary key including the new entity revision id column
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            DROP PRIMARY KEY");

        // Remove column for the single relationship
        $this->addSql("ALTER TABLE " . DB_TABLE_PREFIX  . $name . "
            DROP entityRevisionId");
    }
}
