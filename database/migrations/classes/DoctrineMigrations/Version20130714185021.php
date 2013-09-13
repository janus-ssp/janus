<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130714185021 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    public function up(Schema $schema)
    {
        $userTable = $schema->createTable($this->tablePrefix . 'user');
        $userTable->addOption('engine', 'MyISAM');
        $userTable->addColumn('uid', TYPE::INTEGER, array('autoincrement' => true));
        $userTable->addColumn('userid', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userTable->addColumn('type', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userTable->addColumn('email', TYPE::STRING, array('length' => 320, 'notnull' => false, 'default' => null));
        $userTable->addColumn('active', TYPE::STRING, array('length' => 3, 'fixed' => true, 'notnull' => false, 'default' => 'yes'));
        $userTable->addColumn('`update`', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userTable->addColumn('data', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userTable->addColumn('secret', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userTable->setPrimaryKey(array('uid'));

        $entityArpTable = $schema->createTable($this->tablePrefix . 'arp');
        $entityArpTable->addOption('engine', 'MyISAM');
        $entityArpTable->addColumn('aid', TYPE::INTEGER, array('autoincrement' => true));
        $entityArpTable->addColumn('name', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityArpTable->addColumn('description', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityArpTable->addColumn('is_default', TYPE::BOOLEAN, array('notnull' => false, 'default' => null));
        $entityArpTable->addColumn('attributes', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityArpTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityArpTable->addColumn('updated', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityArpTable->addColumn('deleted', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityArpTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
        $entityArpTable->setPrimaryKey(array('aid'));

        $entityTable = $schema->createTable($this->tablePrefix . 'entity');
        $entityTable->addOption('engine', 'MyISAM');
        $entityTable->addColumn('eid', TYPE::INTEGER);
        $entityTable->addColumn('revisionid', TYPE::INTEGER, array('notnull' => true, 'default' => 0));
        $entityTable->addColumn('arp', TYPE::INTEGER, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('user', TYPE::INTEGER, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('entityid', TYPE::TEXT);
        $entityTable->addColumn('state', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('type', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('expiration', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $entityTable->addColumn('metadataurl', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('metadata_valid_until', TYPE::DATETIME, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('metadata_cache_until', TYPE::DATETIME, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('allowedall', TYPE::STRING, array('length' => 3, 'fixed' => true, 'default' => 'yes'));
        $entityTable->addColumn('manipulation', TYPE::TEXT, array('notnull' => false));
        $entityTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $entityTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
        $entityTable->addColumn('parent', TYPE::INTEGER, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('revisionnote', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $entityTable->addColumn('active', 'string', array('length' => 3, 'default' => 'yes'));
        $entityTable->setPrimaryKey(array('eid', 'revisionid'));

        $entityBlockedEntityRelationTable = $schema->createTable($this->tablePrefix . 'blockedEntity');
        $entityBlockedEntityRelationTable->addOption('engine', 'MyISAM');
        $entityBlockedEntityRelationTable->addColumn('eid', TYPE::INTEGER);
        $entityBlockedEntityRelationTable->addColumn('revisionid', TYPE::INTEGER);
        $entityBlockedEntityRelationTable->addColumn('remoteeid', TYPE::INTEGER);
        $entityBlockedEntityRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityBlockedEntityRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
        $entityBlockedEntityRelationTable->addIndex(array('remoteeid'));

        $entityDisableConsentRelationTable = $schema->createTable($this->tablePrefix . 'disableConsent');
        $entityDisableConsentRelationTable->addOption('engine', 'MyISAM');
        $entityDisableConsentRelationTable->addColumn('eid', TYPE::INTEGER);
        $entityDisableConsentRelationTable->addColumn('revisionid', TYPE::INTEGER);
        $entityDisableConsentRelationTable->addColumn('remoteentityid', TYPE::STRING);
        $entityDisableConsentRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityDisableConsentRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));

        $allowedEntityTable = $schema->createTable($this->tablePrefix . 'allowedEntity');
        $allowedEntityTable->addOption('engine', 'MyISAM');
        $allowedEntityTable->addColumn('eid', TYPE::INTEGER);
        $allowedEntityTable->addColumn('revisionid', TYPE::INTEGER);
        $allowedEntityTable->addColumn('remoteeid', TYPE::INTEGER);
        $allowedEntityTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $allowedEntityTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
        $allowedEntityTable->addIndex(array('remoteeid'));

        $entityMetadataTable = $schema->createTable($this->tablePrefix . 'metadata');
        $entityMetadataTable->addOption('engine', 'MyISAM');
        $entityMetadataTable->addColumn('eid', TYPE::INTEGER);
        $entityMetadataTable->addColumn('revisionid', TYPE::INTEGER);
        $entityMetadataTable->addColumn('`key`', TYPE::STRING, array('length' => 255));
        $entityMetadataTable->addColumn('value', TYPE::TEXT);
        $entityMetadataTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $entityMetadataTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));

        $userDataTable = $schema->createTable($this->tablePrefix . 'userData');
        $userDataTable->addOption('engine', 'MyISAM');
        $userDataTable->addColumn('uid', TYPE::INTEGER);
        $userDataTable->addColumn('`key`', TYPE::STRING, array('length' => 255));
        $userDataTable->addColumn('value', TYPE::STRING, array('length' => 255));
        $userDataTable->addColumn('`update`', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $userDataTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $userDataTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
        $userDataTable->addUniqueIndex(array('uid', '`key`'), 'uid');

        $userMessageTable = $schema->createTable($this->tablePrefix . 'message');
        $userMessageTable->addOption('engine', 'MyISAM');
        $userMessageTable->addColumn('mid', TYPE::INTEGER, array('autoincrement' => true));
        $userMessageTable->addColumn('uid', TYPE::INTEGER);
        $userMessageTable->addColumn('subject', TYPE::TEXT);
        $userMessageTable->addColumn('message', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userMessageTable->addColumn('`from`', TYPE::INTEGER);
        $userMessageTable->addColumn('subscription', TYPE::TEXT);
        $userMessageTable->addColumn('`read`', TYPE::STRING, array('length' => 3, 'default' => 'no'));
        $userMessageTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
        $userMessageTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userMessageTable->setPrimaryKey(array('mid'));

        $userEntityRelationTable = $schema->createTable($this->tablePrefix . 'hasEntity');
        $userEntityRelationTable->addOption('engine', 'MyISAM');
        $userEntityRelationTable->addColumn('uid', TYPE::INTEGER);
        $userEntityRelationTable->addColumn('eid', TYPE::INTEGER, array('notnull' => false, 'default' => null));
        $userEntityRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userEntityRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));

        $userSubscriptionTable = $schema->createTable($this->tablePrefix . 'subscription');
        $userSubscriptionTable->addOption('engine', 'MyISAM');
        $userSubscriptionTable->addColumn('sid', TYPE::INTEGER, array('autoincrement' => true));
        $userSubscriptionTable->addColumn('uid', TYPE::INTEGER);
        $userSubscriptionTable->addColumn('subscription', TYPE::TEXT);
        $userSubscriptionTable->addColumn('type', TYPE::TEXT, array('notnull' => false, 'default' => null));
        $userSubscriptionTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userSubscriptionTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
        $userSubscriptionTable->setPrimaryKey(array('sid'));
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
