<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715061941AddForeignKeyConstraints extends AbstractMigration
{
    /**
     * Adds foreign key constraints to related tables
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");

        $userTable = $schema->getTable(DB_TABLE_PREFIX . 'user');
        $entityArpTable = $schema->getTable(DB_TABLE_PREFIX . 'arp');

        $entityTable = $schema->getTable(DB_TABLE_PREFIX . 'entity');
        $entityTable->addForeignKeyConstraint($entityArpTable, array('arp'), array('aid'), array(), 'FK_B5B24B90FB58124D');
        $entityTable->addForeignKeyConstraint($userTable, array('user'), array('uid'), array(), 'FK_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'blockedEntity');
        $entityBlockedEntityRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C3FFDC7F4FBDA576B5AB769A');

        $entityDisableConsentRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'disableConsent');
        $entityDisableConsentRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C88326594FBDA576B5AB769A');

        $allowedEntityTable = $schema->getTable(DB_TABLE_PREFIX . 'allowedEntity');
        $allowedEntityTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_B71F875B4FBDA576B5AB769A');

        $entityMetadataTable = $schema->getTable(DB_TABLE_PREFIX . 'metadata');
        $entityMetadataTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_3CEF9AA4FBDA576B5AB769A');

        $userDataTable = $schema->getTable(DB_TABLE_PREFIX . 'userData');
        $userDataTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_E766E992539B0606');

        $userMessageTable = $schema->getTable(DB_TABLE_PREFIX . 'message');
        $userMessageTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_560D05E539B0606');
        $userMessageTable->addForeignKeyConstraint($userTable, array('`from`'), array('uid'), array(), 'FK_560D05EB018BCAC');

        $userEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'hasEntity');
        $userEntityRelationTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable(DB_TABLE_PREFIX . 'subscription');
        $userSubscriptionTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_C3A17847539B0606');
    }

    /**
     * Removes foreign key constraints from related tables
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $userTable = $schema->getTable(DB_TABLE_PREFIX . 'user');
        $entityArpTable = $schema->getTable(DB_TABLE_PREFIX . 'arp');

        $entityTable = $schema->getTable(DB_TABLE_PREFIX . 'entity');
        $entityTable->removeForeignKey('FK_B5B24B90FB58124D');
        $entityTable->dropIndex('IDX_B5B24B90FB58124D');
        $entityTable->removeForeignKey('FK_B5B24B908D93D649');
        $entityTable->dropIndex('IDX_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'blockedEntity');
        $entityBlockedEntityRelationTable->removeForeignKey('FK_C3FFDC7F4FBDA576B5AB769A');
        $entityBlockedEntityRelationTable->dropIndex('IDX_C3FFDC7F4FBDA576B5AB769A');

        $entityDisableConsentRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'disableConsent');
        $entityDisableConsentRelationTable->removeForeignKey('FK_C88326594FBDA576B5AB769A');
        $entityDisableConsentRelationTable->dropIndex('IDX_C88326594FBDA576B5AB769A');

        $allowedEntityTable = $schema->getTable(DB_TABLE_PREFIX . 'allowedEntity');
        $allowedEntityTable->removeForeignKey('FK_B71F875B4FBDA576B5AB769A');
        $allowedEntityTable->dropIndex('IDX_B71F875B4FBDA576B5AB769A');

        $entityMetadataTable = $schema->getTable(DB_TABLE_PREFIX . 'metadata');
        $entityMetadataTable->removeForeignKey('FK_3CEF9AA4FBDA576B5AB769A');
        $entityMetadataTable->dropIndex('IDX_3CEF9AA4FBDA576B5AB769A');

        $userDataTable = $schema->getTable(DB_TABLE_PREFIX . 'userData');
        $userDataTable->removeForeignKey('FK_E766E992539B0606');
        $userDataTable->dropIndex('IDX_E766E992539B0606');

        $userMessageTable = $schema->getTable(DB_TABLE_PREFIX . 'message');
        $userMessageTable->removeForeignKey('FK_560D05E539B0606');
        $userMessageTable->dropIndex('IDX_560D05E539B0606');
        $userMessageTable->removeForeignKey('FK_560D05EB018BCAC');
        $userMessageTable->dropIndex('IDX_560D05EB018BCAC');

        $userEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'hasEntity');
        $userEntityRelationTable->removeForeignKey('FK_54A0F93A539B0606');
        $userEntityRelationTable->dropIndex('IDX_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable(DB_TABLE_PREFIX . 'subscription');
        $userSubscriptionTable->removeForeignKey('FK_C3A17847539B0606');
        $userSubscriptionTable->dropIndex('IDX_C3A17847539B0606');
    }
}