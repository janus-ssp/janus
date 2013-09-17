<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715003624 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Adds Primary key to each column (doctrine requires this)
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $entityBlockedEntityRelationTable = $schema->getTable($this->tablePrefix . 'blockedEntity');
        $entityBlockedEntityRelationTable->setPrimaryKey(array('eid', 'revisionid', 'remoteeid'));

        $entityDisableConsentRelationTable = $schema->getTable($this->tablePrefix . 'disableConsent');
        $entityDisableConsentRelationTable->setPrimaryKey(array('eid', 'revisionid', 'remoteentityid'));

        $allowedEntityTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->setPrimaryKey(array('eid', 'revisionid', 'remoteeid'));

        $entityMetadataTable = $schema->getTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->setPrimaryKey(array('eid', 'revisionid', '`key`'));

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->setPrimaryKey(array('uid', '`key`'));

        $userEntityRelationTable = $schema->getTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->setPrimaryKey(array('uid', 'eid'));
    }

    public function down(Schema $schema)
    {
        $entityBlockedEntityRelationTable = $schema->getTable($this->tablePrefix . 'blockedEntity');
        $entityBlockedEntityRelationTable->dropPrimaryKey();

        $entityDisableConsentRelationTable = $schema->getTable($this->tablePrefix . 'disableConsent');
        $entityDisableConsentRelationTable->dropPrimaryKey();

        $allowedEntityTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->dropPrimaryKey();

        $entityMetadataTable = $schema->getTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->dropPrimaryKey();

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->dropPrimaryKey();

        $userEntityRelationTable = $schema->getTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->dropPrimaryKey();
    }
}
