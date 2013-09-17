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
        // Key column is currently too long to be part of primary key so it has to be shortened first
        // It would be even better to fix this by using eid instead of entityid https://github.com/janus-ssp/janus/issues/389
        $entityDisableConsentRelationTable->setPrimaryKey(array('eid', 'revisionid'));

        $allowedEntityTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->setPrimaryKey(array('eid', 'revisionid', 'remoteeid'));

        $entityMetadataTable = $schema->getTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->dropIndex('janus__metadata__eid_revisionid_key');
        $entityMetadataTable->setPrimaryKey(array('eid', 'revisionid', '`key`'));

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->dropIndex('uid');
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
        $entityMetadataTable->addUniqueIndex(array('eid', 'revisionid', '`key`'), 'janus__metadata__eid_revisionid_key');

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->dropPrimaryKey();
        $userDataTable->addUniqueIndex(array('uid', '`key`'), 'uid');

        $userEntityRelationTable = $schema->getTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->dropPrimaryKey();
    }
}
