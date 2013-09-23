<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715061940 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    private $tables = array(
        'allowedEntity',
        'arp',
        'blockedEntity',
        'disableConsent',
        'entity',
        'hasEntity',
        'message',
        'metadata',
        'subscription',
        'user',
        'userData'
    );

    private $backupTableSuffix = '__old';

    /**
     * Convert all tables to InnoDB so it is possible to create foreign keys
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {

            foreach($this->tables as $table) {
                $fullTableName = $this->tablePrefix . $table;
                $fullBackupTableName = $this->tablePrefix . $table . $this->backupTableSuffix;

                $this->addSql("RENAME TABLE $fullTableName TO $fullBackupTableName");
                $this->addSql("CREATE TABLE $fullTableName LIKE $fullBackupTableName");
                $this->addSql("ALTER TABLE $fullTableName ENGINE=InnoDB");
                $this->addSql("INSERT INTO $fullTableName SELECT * FROM $fullBackupTableName");
                // @todo comment this
                $this->addSql("DROP TABLE $fullBackupTableName");
            }
        }
    }

    /**
     * Convert all tables back to MyISAM
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if($this->connection->getDatabasePlatform()->getName() == "mysql") {
            foreach($this->tables as $table) {
                $fullTableName = $this->tablePrefix . $table;
                $fullBackupTableName = $this->tablePrefix . $table . $this->backupTableSuffix;

                $this->addSql("RENAME TABLE $fullTableName TO $fullBackupTableName");
                $this->addSql("CREATE TABLE $fullTableName LIKE $fullBackupTableName");
                $this->addSql("ALTER TABLE $fullTableName ENGINE=MyISAM");
                $this->addSql("INSERT INTO $fullTableName SELECT * FROM $fullBackupTableName");
                // @todo comment this
                $this->addSql("DROP TABLE $fullBackupTableName");
            }
        }
    }
}