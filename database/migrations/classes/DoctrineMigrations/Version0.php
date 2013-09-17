<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version0
 * @package DoctrineMigrations
 */
class Version0 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Create initial schema based on what was in the old janus.sql file with a few exceptions to make it compatible with Doctrine's portable migration style
     * If table exists make a few portability fixes
     */
    public function up(Schema $schema)
    {
        // USER
        if (!$schema->hasTable($this->tablePrefix . 'user')) {
            $userTable = $schema->createTable($this->tablePrefix . 'user');
            $userTable->addOption('engine', 'MyISAM');
            $userTable->addColumn('uid', TYPE::INTEGER, array('autoincrement' => true));
            $userTable->addColumn('userid', TYPE::TEXT, array('notnull' => false, 'default' => null));
            $userTable->addColumn('type', TYPE::TEXT, array('notnull' => false, 'dremoteeidefault' => null));
            $userTable->addColumn('email', TYPE::STRING, array('length' => 320, 'notnull' => false, 'default' => null));
            $userTable->addColumn('active', TYPE::STRING, array('length' => 3, 'fixed' => true, 'notnull' => false, 'default' => 'yes'));
            $userTable->addColumn('`update`', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
            $userTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
            $userTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
            $userTable->addColumn('data', TYPE::TEXT, array('notnull' => false, 'default' => null));
            $userTable->addColumn('secret', TYPE::TEXT, array('notnull' => false, 'default' => null));
            $userTable->setPrimaryKey(array('uid'));
        }

        // ARP
        if (!$schema->hasTable($this->tablePrefix . 'arp')) {
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
        }

        // ENTITY
        if ($schema->hasTable($this->tablePrefix . 'entity')) {
            // Doctrine is not (very) compatible with ENUM fields, so change them to char fields
            // see: http://docs.doctrine-project.org/en/latest/cookbook/mysql-enums.html
            // Since MySQL qas they only supported database type until now this can be done in plain in SQL
            $this->addSql("ALTER TABLE {$this->tablePrefix}entity CHANGE `active` `active` CHAR(3) NOT NULL DEFAULT 'yes'");
        } else {
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
            $entityTable->addColumn('active', TYPE::STRING, array('length' => 3, 'default' => 'yes'));
            $entityTable->setPrimaryKey(array('eid', 'revisionid'));
        }

        // BLOCKED ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'blockedEntity')) {
            $entityBlockedEntityRelationTable = $schema->createTable($this->tablePrefix . 'blockedEntity');
            $entityBlockedEntityRelationTable->addOption('engine', 'MyISAM');
            $entityBlockedEntityRelationTable->addColumn('eid', TYPE::INTEGER);
            $entityBlockedEntityRelationTable->addColumn('revisionid', TYPE::INTEGER);
            $entityBlockedEntityRelationTable->addColumn('remoteeid', TYPE::INTEGER);
            $entityBlockedEntityRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $entityBlockedEntityRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
            $entityBlockedEntityRelationTable->addIndex(array('remoteeid'), 'remoteeid');
        }

        // DISABLE CONSENT
        if (!$schema->hasTable($this->tablePrefix . 'disableConsent')) {
            $entityDisableConsentRelationTable = $schema->createTable($this->tablePrefix . 'disableConsent');
            $entityDisableConsentRelationTable->addOption('engine', 'MyISAM');
            $entityDisableConsentRelationTable->addColumn('eid', TYPE::INTEGER);
            $entityDisableConsentRelationTable->addColumn('revisionid', TYPE::INTEGER);
            $entityDisableConsentRelationTable->addColumn('remoteentityid', TYPE::TEXT);
            $entityDisableConsentRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $entityDisableConsentRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
        }

        // ALLOWED ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'allowedEntity')) {
            $allowedEntityTable = $schema->createTable($this->tablePrefix . 'allowedEntity');
            $allowedEntityTable->addOption('engine', 'MyISAM');
            $allowedEntityTable->addColumn('eid', TYPE::INTEGER);
            $allowedEntityTable->addColumn('revisionid', TYPE::INTEGER);
            $allowedEntityTable->addColumn('remoteeid', TYPE::INTEGER);
            $allowedEntityTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $allowedEntityTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
            $allowedEntityTable->addIndex(array('remoteeid'), 'remoteeid');
        }

        // METADATA
        if ($schema->hasTable($this->tablePrefix . 'metadata')) {
            // Key does not have to be a text value, this is way too long is cannot be used in keys
            // Also key cannot be null
            // Since MySQL qas they only supported database type until now this can be done in plain in SQL
            $this->addSql("ALTER TABLE {$this->tablePrefix}metadata CHANGE `key` `key` VARCHAR(255) NOT NULL");
        } else {
            $entityMetadataTable = $schema->createTable($this->tablePrefix . 'metadata');
            $entityMetadataTable->addOption('engine', 'MyISAM');
            $entityMetadataTable->addColumn('eid', TYPE::INTEGER);
            $entityMetadataTable->addColumn('revisionid', TYPE::INTEGER);
            $entityMetadataTable->addColumn('`key`', TYPE::STRING, array('length' => 255));
            $entityMetadataTable->addColumn('value', TYPE::TEXT);
            $entityMetadataTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $entityMetadataTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
            $entityMetadataTable->addUniqueIndex(array('eid', 'revisionid', '`key`'), 'janus__metadata__eid_revisionid_key');
        }

        // USER DATA
        if (!$schema->hasTable($this->tablePrefix . 'userData')) {
            $userDataTable = $schema->createTable($this->tablePrefix . 'userData');
            $userDataTable->addOption('engine', 'MyISAM');
            $userDataTable->addColumn('uid', TYPE::INTEGER);
            $userDataTable->addColumn('`key`', TYPE::STRING, array('length' => 255));
            $userDataTable->addColumn('value', TYPE::STRING, array('length' => 255));
            $userDataTable->addColumn('`update`', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $userDataTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true));
            $userDataTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true));
            $userDataTable->addUniqueIndex(array('uid', '`key`'), 'uid');
        }

        // MESSAGE
        if ($schema->hasTable($this->tablePrefix . 'message')) {
            // Doctrine is not (very) compatible with ENUM fields, so change them to char fields
            // see: http://docs.doctrine-project.org/en/latest/cookbook/mysql-enums.html
            // Since MySQL qas they only supported database type until now this can be done in plain in SQL
            $this->addSql("ALTER TABLE {$this->tablePrefix}message CHANGE `read` `read` CHAR(3) NOT NULL DEFAULT 'no'");

        } else {
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
        }

        // HAS ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'hasEntity')) {
            $userEntityRelationTable = $schema->createTable($this->tablePrefix . 'hasEntity');
            $userEntityRelationTable->addOption('engine', 'MyISAM');
            $userEntityRelationTable->addColumn('uid', TYPE::INTEGER);
            $userEntityRelationTable->addColumn('eid', TYPE::INTEGER, array('notnull' => false, 'default' => null));
            $userEntityRelationTable->addColumn('created', TYPE::STRING, array('length' => 25, 'fixed' => true, 'notnull' => false, 'default' => null));
            $userEntityRelationTable->addColumn('ip', TYPE::STRING, array('length' => 39, 'fixed' => true, 'notnull' => false, 'default' => null));
        }

        // SUBSCRIPTION
        if (!$schema->hasTable($this->tablePrefix . 'subscription')) {
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
    }

    public function down(Schema $schema)
    {
        // No down migration
    }
}
