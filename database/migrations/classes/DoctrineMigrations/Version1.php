<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;

/**
 * Class Version1
 * @package DoctrineMigrations
 */
class Version1 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * Create initial schema based as it was before Doctrine was introduced (docs/janus.sql file ) with a few exceptions to make it compatible with Doctrine's portable migration style
     * If table exists make a few portability fixes
     *
     * Note: Doctrine uses length to determin which text type to use (in MySQL)
     * To stay as close as possible to the original schema, lengths are explicitely set
     *
     * Mapping
     * length <= 255        -> TINYTEXT
     * length <= 65532      -> TEXT
     * length <= 16777215   -> MEDIUMTEXT
     */
    public function up(Schema $schema)
    {
        // USER
        if (!$schema->hasTable($this->tablePrefix . 'user')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}user (
                    uid int(11) NOT NULL AUTO_INCREMENT,
                    userid text,
                    `type` text,
                    email varchar(320) DEFAULT NULL,
                    active char(3) DEFAULT 'yes',
                    `update` char(25) DEFAULT NULL,
                    created char(25) DEFAULT NULL,
                    ip char(39) DEFAULT NULL,
                    `data` text,
                    secret text,
                    PRIMARY KEY (uid)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
          ");
        }

        // ARP
        if (!$schema->hasTable($this->tablePrefix . 'arp')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}arp (
                    aid int(11) NOT NULL AUTO_INCREMENT,
                    `name` text,
                    description text,
                    is_default boolean,
                    attributes text,
                    created char(25) NOT NULL,
                    updated char(25) NOT NULL,
                    deleted char(25) NOT NULL,
                    ip char(39) NOT NULL,
                    PRIMARY KEY (aid)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
          ");
        }

        // ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'entity')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}entity (
                    eid int(11) NOT NULL,
                    entityid text NOT NULL,
                    revisionid int(11) DEFAULT NULL,
                    state text,
                    `type` text,
                    expiration char(25) DEFAULT NULL,
                    metadataurl text,
                    metadata_valid_until datetime DEFAULT NULL,
                    metadata_cache_until datetime DEFAULT NULL,
                    allowedall char(3) NOT NULL DEFAULT 'yes',
                    arp int(11) DEFAULT NULL,
                    `user` int(11) DEFAULT NULL,
                    created char(25) DEFAULT NULL,
                    ip char(39) DEFAULT NULL,
                    parent int(11) DEFAULT NULL,
                    revisionnote text,
                    active ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
                    UNIQUE KEY eid (eid,revisionid),
                    UNIQUE KEY janus__entity__eid_revisionid (eid,revisionid)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
          ");
        }

        // BLOCKED ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'blockedEntity')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}blockedEntity (
                    eid int(11) NOT NULL,
                    revisionid int(11) NOT NULL,
                    remoteentityid text NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
          ");
        }

        // DISABLE CONSENT
        if (!$schema->hasTable($this->tablePrefix . 'disableConsent')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}disableConsent (
                    eid int(11) NOT NULL,
                    revisionid int(11) NOT NULL,
                    remoteentityid text NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // ALLOWED ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'allowedEntity')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}allowedEntity (
                    eid int(11) NOT NULL,
                    revisionid int(11) NOT NULL,
                    remoteentityid text NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL
                  )
                  ENGINE=MyISAM
                  DEFAULT CHARSET=utf8;
            ");
        }

        // METADATA
        if (!$schema->hasTable($this->tablePrefix . 'metadata')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}metadata (
                    eid int(11) NOT NULL,
                    revisionid int(11) NOT NULL,
                    `key` text NOT NULL,
                    `value` text NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL,
                    UNIQUE KEY janus__metadata__eid_revisionid_key (eid,revisionid,`key`(50))
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // USER DATA
        if (!$schema->hasTable($this->tablePrefix . 'userData')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}userData (
                    uid int(11) NOT NULL,
                    `key` varchar(255) NOT NULL,
                    `value` varchar(255) NOT NULL,
                    `update` char(25) NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL,
                    UNIQUE KEY uid (uid,`key`)
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // MESSAGE
        if (!$schema->hasTable($this->tablePrefix . 'message')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}message (
                    mid int(11) NOT NULL AUTO_INCREMENT,
                    uid int(11) NOT NULL,
                    `subject` text NOT NULL,
                    message text,
                    `from` int(11) NOT NULL,
                    subscription text NOT NULL,
                    `read` enum('yes','no') DEFAULT 'no',
                    created char(25) NOT NULL,
                    ip char(39) DEFAULT NULL,
                    PRIMARY KEY (mid)
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // HAS ENTITY
        if (!$schema->hasTable($this->tablePrefix . 'hasEntity')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}hasEntity (
                    uid int(11) NOT NULL,
                    eid int(11) DEFAULT NULL,
                    created char(25) DEFAULT NULL,
                    ip char(39) DEFAULT NULL
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // SUBSCRIPTION
        if (!$schema->hasTable($this->tablePrefix . 'subscription')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}subscription (
                    sid int(11) NOT NULL AUTO_INCREMENT,
                    uid int(11) NOT NULL,
                    subscription text NOT NULL,
                    `type` text,
                    created char(25) DEFAULT NULL,
                    ip char(39) DEFAULT NULL,
                    PRIMARY KEY (sid)
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
          ");
        }

        // ATTRIBUTE
        if (!$schema->hasTable($this->tablePrefix . 'attribute')) {
            $this->addSql("
                CREATE TABLE {$this->tablePrefix}attribute (
                    eid int(11) NOT NULL,
                    revisionid int(11) NOT NULL,
                    `key` text NOT NULL,
                    `value` text NOT NULL,
                    created char(25) NOT NULL,
                    ip char(39) NOT NULL
                )
                ENGINE=MyISAM
                DEFAULT CHARSET=utf8;
            ");
        }
    }

    /**
     * NOTE: migrating down from version 1 is only useful with an existing database
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //
    }
}
