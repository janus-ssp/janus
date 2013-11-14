<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * The ARP is no longer standalone, but integral part of the EntityRevision
 */
class Version20131114150840RemoveArpToEntityRevision extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `" . DB_TABLE_PREFIX . "entityRevision`
              ADD `arp_attributes` text COMMENT '(DC2Type:array)'
        ");
        $this->addSql("
            UPDATE `" . DB_TABLE_PREFIX . "entityRevision` ENTITY_REVISION SET `arp_attributes` =
              (select `attributes` from `" . DB_TABLE_PREFIX . "arp` ARP where ARP.`aid` = ENTITY_REVISION.`arp` )
        ");
        $this->addSql("
            ALTER TABLE `" . DB_TABLE_PREFIX . "entityRevision` DROP FOREIGN KEY `FK_72BCD7F2FB58124D`
        ");
        $this->addSql("
            ALTER TABLE `" . DB_TABLE_PREFIX . "entityRevision` DROP INDEX `IDX_72BCD7F2FB58124D`
        ");
        $this->addSql("
            ALTER TABLE `" . DB_TABLE_PREFIX . "entityRevision` DROP COLUMN `arp`
        ");
        $this->addSql("
            DROP TABLE `" . DB_TABLE_PREFIX . "arp`
        ");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
