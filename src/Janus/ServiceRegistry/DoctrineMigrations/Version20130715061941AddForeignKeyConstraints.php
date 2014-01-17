<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

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
        $this->addForeignKeyConstraint($schema, 'connection', 'user', array('user'), array('uid'), array('onMissing' => 'SETNULL'), 'FK_B66402878D93D649');

        $this->addForeignKeyConstraint($schema, 'connectionRevision', 'connection', array('eid'), array('id'), array('onDelete' => 'CASCADE'), 'FK_72BCD7F24FBDA576');
        $this->addForeignKeyConstraint($schema, 'connectionRevision', 'arp', array('arp'), array('aid'), array('onMissing' => 'SETNULL'), 'FK_72BCD7F2FB58124D');
        $this->addForeignKeyConstraint($schema, 'connectionRevision', 'user', array('user'), array('uid'), array('onMissing' => 'SETNULL'), 'FK_72BCD7F28D93D649');

        $this->addForeignKeyConstraint($schema, 'blockedConnection', 'connectionRevision', array('connectionRevisionId'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_C3FFDC7F549045D9');
        $this->addForeignKeyConstraint($schema, 'blockedConnection', 'connection', array('remoteeid'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_C3FFDC7F3C2FCD2');

        $this->addForeignKeyConstraint($schema, 'disableConsent', 'connectionRevision', array('connectionRevisionId'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_C8832659549045D9');
        $this->addForeignKeyConstraint($schema, 'disableConsent', 'connection', array('remoteeid'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_C88326593C2FCD2');

        $this->addForeignKeyConstraint($schema, 'allowedConnection', 'connectionRevision', array('connectionRevisionId'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_B71F875B549045D9');
        $this->addForeignKeyConstraint($schema, 'allowedConnection', 'connection', array('remoteeid'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_B71F875B3C2FCD2');

        $this->addForeignKeyConstraint($schema, 'metadata', 'connectionRevision', array('connectionRevisionId'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_3CEF9AA549045D9');

        $this->addForeignKeyConstraint($schema, 'userData', 'user', array('uid'), array('uid'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_E766E992539B0606');

        $this->addForeignKeyConstraint($schema, 'message', 'user', array('uid'), array('uid'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_560D05E539B0606');
        $this->addForeignKeyConstraint($schema, 'message', 'user', array('`from`'), array('uid'), array('onMissing' => 'DELETE'), 'FK_560D05EB018BCAC');

        $this->addForeignKeyConstraint($schema, 'hasConnection', 'connection', array('eid'), array('id'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_54A0F93A4FBDA576');
        $this->addForeignKeyConstraint($schema, 'hasConnection', 'user', array('uid'), array('uid'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_54A0F93A539B0606');

        $this->addForeignKeyConstraint($schema, 'subscription', 'user', array('uid'), array('uid'), array('onDelete' => 'CASCADE', 'onMissing' => 'DELETE'), 'FK_C3A17847539B0606');
    }

    /**
     * @param Schema $schema
     * @param string $tableName
     * @param string $foreignTableName
     * @param array $fieldNames
     * @param array $foreignFieldNames
     * @param string $name
     * @param array $options
     */
    private function addForeignKeyConstraint(
        Schema $schema,
        $tableName,
        $foreignTableName,
        array $fieldNames,
        array $foreignFieldNames,
        array $options,
        $name
    )
    {
        $tableNamePrefixed = DB_TABLE_PREFIX . $tableName;
        $foreignTableNamePrefixed = DB_TABLE_PREFIX . $foreignTableName;

        $fieldName = implode(',', $fieldNames);
        $foreignFieldName = implode(',', $foreignFieldNames);

        // Set relation field to null for non existing references
        if (isset($options['onMissing']) && $options['onMissing'] === 'SETNULL') {
            $this->addSql("UPDATE {$tableNamePrefixed} AS {$tableName}
                LEFT JOIN {$foreignTableNamePrefixed} AS {$foreignTableName}
                    ON {$foreignTableName}.{$foreignFieldName} = {$tableName}.{$fieldName}
                SET {$tableName}.{$fieldName} = NULL
                    WHERE {$foreignTableName}.{$foreignFieldName} IS NULL");
        }

        // DELETE record if foreign record is missing
        if (isset($options['onMissing']) && $options['onMissing'] === 'DELETE') {
            $this->addSql("DELETE {$tableName}
                FROM {$tableNamePrefixed} AS {$tableName}
                LEFT JOIN {$foreignTableNamePrefixed} AS {$foreignTableName}
                    ON {$foreignTableName}.{$foreignFieldName} = {$tableName}.{$fieldName}
                    WHERE {$foreignTableName}.{$foreignFieldName} IS NULL");
        }

        // Add Constraint
        $query = "ALTER TABLE {$tableNamePrefixed}
            ADD CONSTRAINT {$name}
            FOREIGN KEY ({$fieldName})
            REFERENCES {$foreignTableNamePrefixed} ({$foreignFieldName})";
        if (isset($options['onDelete']) && $options['onDelete'] === 'CASCADE') {
              $query .= " ON DELETE CASCADE";
        }
        $this->addSql($query);

        // Add Key
        $indexName = str_replace('FK', 'IDX', $name);
        $this->addSql("CREATE INDEX {$indexName} ON {$tableNamePrefixed} ({$fieldName})");
    }

    /**
     * Removes foreign key constraints from related tables
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $connectionTable = $schema->getTable(DB_TABLE_PREFIX . 'connection');
        $this->removeForeignKeyAndIndex($connectionTable, 'FK_B66402878D93D649');
        $connectionRevisionTable = $schema->getTable(DB_TABLE_PREFIX . 'connectionRevision');
        $this->removeForeignKeyAndIndex($connectionRevisionTable, 'FK_72BCD7F24FBDA576');
        $this->removeForeignKeyAndIndex($connectionRevisionTable, 'FK_72BCD7F2FB58124D');
        $this->removeForeignKeyAndIndex($connectionRevisionTable, 'FK_72BCD7F28D93D649');

        $connectionBlockedConnectionRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'blockedConnection');
        $this->removeForeignKeyAndIndex($connectionBlockedConnectionRelationTable, 'FK_C3FFDC7F549045D9');
        $this->removeForeignKeyAndIndex($connectionBlockedConnectionRelationTable, 'FK_C3FFDC7F3C2FCD2');

        $connectionDisableConsentRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'disableConsent');
        $this->removeForeignKeyAndIndex($connectionDisableConsentRelationTable, 'FK_C8832659549045D9');
        $this->removeForeignKeyAndIndex($connectionDisableConsentRelationTable, 'FK_C88326593C2FCD2');

        $allowedConnectionTable = $schema->getTable(DB_TABLE_PREFIX . 'allowedConnection');
        $this->removeForeignKeyAndIndex($allowedConnectionTable, 'FK_B71F875B549045D9');
        $this->removeForeignKeyAndIndex($allowedConnectionTable, 'FK_B71F875B3C2FCD2');

        $connectionMetadataTable = $schema->getTable(DB_TABLE_PREFIX . 'metadata');
        $this->removeForeignKeyAndIndex($connectionMetadataTable, 'FK_3CEF9AA549045D9');

        $userDataTable = $schema->getTable(DB_TABLE_PREFIX . 'userData');
        $this->removeForeignKeyAndIndex($userDataTable, 'FK_E766E992539B0606');

        $userMessageTable = $schema->getTable(DB_TABLE_PREFIX . 'message');
        $this->removeForeignKeyAndIndex($userMessageTable, 'FK_560D05E539B0606');
        $this->removeForeignKeyAndIndex($userMessageTable, 'FK_560D05EB018BCAC');

        $userConnectionRelationTable = $schema->getTable(DB_TABLE_PREFIX . 'hasConnection');
        $this->removeForeignKeyAndIndex($userConnectionRelationTable, 'FK_54A0F93A4FBDA576');
        $this->removeForeignKeyAndIndex($userConnectionRelationTable, 'FK_54A0F93A539B0606');

        $userSubscriptionTable = $schema->getTable(DB_TABLE_PREFIX . 'subscription');
        $this->removeForeignKeyAndIndex($userSubscriptionTable, 'FK_C3A17847539B0606');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param string $name
     */
    private function removeForeignKeyAndIndex(\Doctrine\DBAL\Schema\Table $table, $name)
    {
        $table->removeForeignKey($name);
        $table->dropIndex(str_replace('FK','IDX', $name));
    }
}