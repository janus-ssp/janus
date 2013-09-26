<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Create a separate list of unique entities
 *
 * Class Version20130915175753
 * @package DoctrineMigrations
 */
class Version20130915175753 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    public function up(Schema $schema)
    {
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            $this->addSql("SET FOREIGN_KEY_CHECKS = 0");
        }

        // Create table for unique entities
        $entityIdTable = $schema->createTable($this->tablePrefix . 'entityId');
        $entityIdTable->addOption('engine', 'InnoDB');
        $entityIdTable->addColumn('eid', TYPE::INTEGER, array('autoincrement' => true));
        $entityIdTable->addColumn('entityid', TYPE::STRING, array('length' => 255));
        $entityIdTable->setPrimaryKey(array('eid'));
        $entityIdTable->addUniqueIndex(array('entityid'), 'entityid');

        // Add references to unique entities
        $entityIdTable = $schema->getTable($this->tablePrefix . 'entityId');

        $schema->getTable($this->tablePrefix . 'entity')
            ->addForeignKeyConstraint($entityIdTable, array('eid'), array('eid'), array(), 'FK_B5B24B904FBDA576');

        $schema->getTable($this->tablePrefix . 'allowedEntity')
            ->addForeignKeyConstraint($entityIdTable, array('remoteeid'), array('eid'), array(), 'FK_B71F875B3C2FCD2');

        $schema->getTable($this->tablePrefix . 'blockedEntity')
            ->addForeignKeyConstraint($entityIdTable, array('remoteeid'), array('eid'), array(), 'FK_C3FFDC7F3C2FCD2');

        $schema->getTable($this->tablePrefix . 'disableConsent')
            ->addForeignKeyConstraint($entityIdTable, array('remoteeid'), array('eid'), array(), 'FK_C88326593C2FCD2');
    }

    public function down(Schema $schema)
    {
        // Remove table
        $schema->dropTable($this->tablePrefix . 'entityId');

        // Remove foreign keys
        $entityTable = $schema->getTable($this->tablePrefix . 'entity');
        $entityTable->removeForeignKey('FK_B5B24B904FBDA576');
        $entityTable->dropIndex('IDX_B5B24B904FBDA576');

        $allowedEntityRelationTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityRelationTable->removeForeignKey('FK_B71F875B3C2FCD2');
        $allowedEntityRelationTable->dropIndex('IDX_B71F875B3C2FCD2');

        $blockedEntityRelationTable = $schema->getTable($this->tablePrefix . 'blockedEntity');
        $blockedEntityRelationTable->removeForeignKey('FK_C3FFDC7F3C2FCD2');
        $blockedEntityRelationTable->dropIndex('IDX_C3FFDC7F3C2FCD2');

        $disableConsentRelationTable = $schema->getTable($this->tablePrefix . 'disableConsent');
        $disableConsentRelationTable->removeForeignKey('FK_C88326593C2FCD2');
        $disableConsentRelationTable->dropIndex('IDX_C88326593C2FCD2');
    }
}