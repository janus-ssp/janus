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
        // todo check if this works
        $this->addSql("SET FOREIGN_KEY_CHECKS = 0");

        $entityTable = $schema->getTable(DB_TABLE_PREFIX . 'entity');
        $userTable = $schema->getTable(DB_TABLE_PREFIX . 'user');
        $entityArpTable = $schema->getTable(DB_TABLE_PREFIX . 'arp');

        $entityRevisionTable = $schema->getTable(DB_TABLE_PREFIX . 'entityRevision');
        $entityRevisionTable->addForeignKeyConstraint($entityTable, array('eid'), array('eid'), array('onDelete' => 'CASCADE'), 'FK_72BCD7F24FBDA576');
        $entityRevisionTable->addForeignKeyConstraint($entityArpTable, array('arp'), array('aid'), array(), 'FK_B5B24B90FB58124D');
        $entityRevisionTable->addForeignKeyConstraint($userTable, array('user'), array('uid'), array(), 'FK_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'blockedEntity');
        $entityBlockedEntityRelationTable->addForeignKeyConstraint($entityRevisionTable, array('entityRevisionId'), array('id'), array('onDelete' => 'CASCADE'), 'FK_C3FFDC7F549045D9');
        $entityBlockedEntityRelationTable->addForeignKeyConstraint($entityTable, array('remoteeid'), array('eid'), array('onDelete' => 'CASCADE'), 'FK_C3FFDC7F3C2FCD2');

        $entityDisableConsentRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'disableConsent');
        $entityDisableConsentRelationTable->addForeignKeyConstraint($entityRevisionTable, array('entityRevisionId'), array('id'), array('onDelete' => 'CASCADE'), 'FK_C8832659549045D9');
        $entityDisableConsentRelationTable->addForeignKeyConstraint($entityTable, array('remoteeid'), array('eid'), array('onDelete' => 'CASCADE'), 'FK_C88326593C2FCD2');

        $allowedEntityTable = $schema->getTable(DB_TABLE_PREFIX . 'allowedEntity');
        $allowedEntityTable->addForeignKeyConstraint($entityRevisionTable, array('entityRevisionId'), array('id'), array('onDelete' => 'CASCADE'), 'FK_B71F875B549045D9');
        $allowedEntityTable->addForeignKeyConstraint($entityTable, array('remoteeid'), array('eid'), array('onDelete' => 'CASCADE'), 'FK_B71F875B3C2FCD2');

        $entityMetadataTable = $schema->getTable(DB_TABLE_PREFIX . 'metadata');
        $entityMetadataTable->addForeignKeyConstraint($entityRevisionTable, array('entityRevisionId'), array('id'), array('onDelete' => 'CASCADE'), 'FK_3CEF9AA549045D9');

        $userDataTable = $schema->getTable(DB_TABLE_PREFIX . 'userData');
        $userDataTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array('onDelete' => 'CASCADE'), 'FK_E766E992539B0606');

        $userMessageTable = $schema->getTable(DB_TABLE_PREFIX . 'message');
        $userMessageTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array('onDelete' => 'CASCADE'), 'FK_560D05E539B0606');
        $userMessageTable->addForeignKeyConstraint($userTable, array('`from`'), array('uid'), array(), 'FK_560D05EB018BCAC');

        $userEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'hasEntity');
        $userEntityRelationTable->addForeignKeyConstraint($entityTable, array('eid'), array('eid'), array('onDelete' => 'CASCADE'), 'FK_54A0F93A4FBDA576');
        $userEntityRelationTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array('onDelete' => 'CASCADE'), 'FK_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable(DB_TABLE_PREFIX . 'subscription');
        $userSubscriptionTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array('onDelete' => 'CASCADE'), 'FK_C3A17847539B0606');
    }

    /**
     * Removes foreign key constraints from related tables
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $entityRevisionTable = $schema->getTable(DB_TABLE_PREFIX . 'entityRevision');
        $entityRevisionTable->removeForeignKey('FK_72BCD7F24FBDA576');
        $entityRevisionTable->dropIndex('IDX_72BCD7F24FBDA576');
        $entityRevisionTable->removeForeignKey('FK_B5B24B90FB58124D');
        $entityRevisionTable->dropIndex('IDX_B5B24B90FB58124D');
        $entityRevisionTable->removeForeignKey('FK_B5B24B908D93D649');
        $entityRevisionTable->dropIndex('IDX_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'blockedEntity');
        $entityBlockedEntityRelationTable->removeForeignKey('FK_C3FFDC7F549045D9');
        $entityBlockedEntityRelationTable->dropIndex('IDX_C3FFDC7F549045D9');
        $entityRevisionTable->removeForeignKey('FK_C3FFDC7F3C2FCD2');
        $entityRevisionTable->dropIndex('IDX_C3FFDC7F3C2FCD2');

        $entityDisableConsentRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'disableConsent');
        $entityDisableConsentRelationTable->removeForeignKey('FK_C8832659549045D9');
        $entityDisableConsentRelationTable->dropIndex('IDX_C8832659549045D9');
        $entityRevisionTable->removeForeignKey('FK_C8832659549045D9');
        $entityRevisionTable->dropIndex('IDX_C8832659549045D9');

        $allowedEntityTable = $schema->getTable(DB_TABLE_PREFIX . 'allowedEntity');
        $allowedEntityTable->removeForeignKey('FK_B71F875B549045D9');
        $allowedEntityTable->dropIndex('IDX_B71F875B549045D9');
        $entityRevisionTable->removeForeignKey('FK_B71F875B549045D9');
        $entityRevisionTable->dropIndex('IDX_B71F875B549045D9');

        $entityMetadataTable = $schema->getTable(DB_TABLE_PREFIX . 'metadata');
        $entityMetadataTable->removeForeignKey('FK_3CEF9AA549045D9');
        $entityMetadataTable->dropIndex('IDX_3CEF9AA549045D9');

        $userDataTable = $schema->getTable(DB_TABLE_PREFIX . 'userData');
        $userDataTable->removeForeignKey('FK_E766E992539B0606');
        $userDataTable->dropIndex('IDX_E766E992539B0606');

        $userMessageTable = $schema->getTable(DB_TABLE_PREFIX . 'message');
        $userMessageTable->removeForeignKey('FK_560D05E539B0606');
        $userMessageTable->dropIndex('IDX_560D05E539B0606');
        $userMessageTable->removeForeignKey('FK_560D05EB018BCAC');
        $userMessageTable->dropIndex('IDX_560D05EB018BCAC');

        $userEntityRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'hasEntity');
        $userEntityRelationTable->removeForeignKey('FK_54A0F93A4FBDA576');
        $userEntityRelationTable->dropIndex('IDX_FK_54A0F93A4FBDA576');
        $userEntityRelationTable->removeForeignKey('FK_54A0F93A539B0606');
        $userEntityRelationTable->dropIndex('IDX_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable(DB_TABLE_PREFIX . 'subscription');
        $userSubscriptionTable->removeForeignKey('FK_C3A17847539B0606');
        $userSubscriptionTable->dropIndex('IDX_C3A17847539B0606');
    }
}