<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130715061940 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Adds foreign key constraints to related tables
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Convert all tables to InnoDB so it is possible to create foreign keys
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'allowedEntity ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'arp ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'blockedEntity ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'disableConsent ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'entity ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'hasEntity ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'message ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'metadata ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'subscription ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'user ENGINE=InnoDB;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'userData ENGINE=InnoDB;');
        }

        $userTable = $schema->getTable($this->tablePrefix . 'user');
        $entityArpTable = $schema->getTable($this->tablePrefix . 'arp');

        $entityTable = $schema->getTable($this->tablePrefix . 'entity');
        $entityTable->addForeignKeyConstraint($entityArpTable, array('arp'), array('aid'), array(), 'FK_B5B24B90FB58124D');
        $entityTable->addForeignKeyConstraint($userTable, array('user'), array('uid'), array(), 'FK_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable($this->tablePrefix . 'blockedEntity');
        $entityBlockedEntityRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C3FFDC7F4FBDA576B5AB769A');
        $entityBlockedEntityRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C3FFDC7F4FBDA576B5AB769A');

        $entityDisableConsentRelationTable = $schema->getTable($this->tablePrefix . 'disableConsent');
        $entityDisableConsentRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C88326594FBDA576B5AB769A');
        $entityDisableConsentRelationTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_C88326594FBDA576B5AB769A');

        $allowedEntityTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_B71F875B4FBDA576B5AB769A');

        $entityMetadataTable = $schema->getTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->addForeignKeyConstraint($entityTable, array('eid', 'revisionid'), array('eid', 'revisionid'), array(), 'FK_3CEF9AA4FBDA576B5AB769A');

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_E766E992539B0606');
        $userDataTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_E766E992539B0606');

        $userMessageTable = $schema->getTable($this->tablePrefix . 'message');
        $userMessageTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_560D05E539B0606');

        $userEntityRelationTable = $schema->getTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_54A0F93A539B0606');
        $userEntityRelationTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable($this->tablePrefix . 'subscription');
        $userSubscriptionTable->addForeignKeyConstraint($userTable, array('uid'), array('uid'), array(), 'FK_C3A17847539B0606');
    }

    /**
     * Removes foreign key constraints from related tables
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $userTable = $schema->getTable($this->tablePrefix . 'user');
        $entityArpTable = $schema->getTable($this->tablePrefix . 'arp');

        $entityTable = $schema->getTable($this->tablePrefix . 'entity');
        $entityTable->removeForeignKey('FK_B5B24B90FB58124D');
        $entityTable->removeForeignKey('FK_B5B24B908D93D649');

        $entityBlockedEntityRelationTable = $schema->getTable($this->tablePrefix . 'blockedEntity');
        $entityBlockedEntityRelationTable->removeForeignKey('FK_C3FFDC7F4FBDA576B5AB769A');
        $entityBlockedEntityRelationTable->removeForeignKey('FK_C3FFDC7F4FBDA576B5AB769A');

        $entityDisableConsentRelationTable = $schema->getTable($this->tablePrefix . 'disableConsent');
        $entityDisableConsentRelationTable->removeForeignKey('FK_C88326594FBDA576B5AB769A');
        $entityDisableConsentRelationTable->removeForeignKey('FK_C88326594FBDA576B5AB769A');

        $allowedEntityTable = $schema->getTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->removeForeignKey('FK_B71F875B4FBDA576B5AB769A');

        $entityMetadataTable = $schema->getTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->removeForeignKey('FK_3CEF9AA4FBDA576B5AB769A');

        $userDataTable = $schema->getTable($this->tablePrefix . 'userData');
        $userDataTable->removeForeignKey('FK_E766E992539B0606');
        $userDataTable->removeForeignKey('FK_E766E992539B0606');

        $userMessageTable = $schema->getTable($this->tablePrefix . 'message');
        $userMessageTable->removeForeignKey('FK_560D05E539B0606');

        $userEntityRelationTable = $schema->getTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->removeForeignKey('FK_54A0F93A539B0606');
        $userEntityRelationTable->removeForeignKey('FK_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable($this->tablePrefix . 'subscription');
        $userSubscriptionTable->removeForeignKey('FK_C3A17847539B0606');

        // Convert all tables to InnoDB so it is possible to create foreign keys
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'allowedEntity ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'arp ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'blockedEntity ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'disableConsent ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'entity ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'hasEntity ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'message ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'metadata ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'subscription ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'user ENGINE=MyISAM;');
            $this->addSql('ALTER TABLE ' . $this->tablePrefix . 'userData ENGINE=MyISAM;');
        }
    }
}
