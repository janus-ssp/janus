<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * The ARP is no longer standalone, but integral part of the EntityRevision
 */
class Version20131114150840RemoveArpToEntityRevision extends JanusMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `" . $this->getTablePrefix() . "connectionRevision`
              ADD `arp_attributes` text COMMENT '(DC2Type:array)'
        ");
        $this->addSql("
            UPDATE `" . $this->getTablePrefix() . "connectionRevision` CONNECTION_REVISION SET `arp_attributes` =
              (select `attributes` from `" . $this->getTablePrefix() . "arp` ARP where ARP.`aid` = CONNECTION_REVISION.`arp` )
        ");
        $this->addSql("
            UPDATE `" . $this->getTablePrefix() . "connectionRevision` CONNECTION_REVISION SET `arp_attributes` = 'N;'
              where CONNECTION_REVISION.`arp_attributes` IS NULL
        ");
        $this->addSql("
            ALTER TABLE `" . $this->getTablePrefix() . "connectionRevision` DROP FOREIGN KEY `FK_72BCD7F2FB58124D`
        ");
        $this->addSql("
            ALTER TABLE `" . $this->getTablePrefix() . "connectionRevision` DROP INDEX `IDX_72BCD7F2FB58124D`
        ");
        $this->addSql("
            ALTER TABLE `" . $this->getTablePrefix() . "connectionRevision` DROP COLUMN `arp`
        ");
        $this->addSql("
            DROP TABLE `" . $this->getTablePrefix() . "arp`
        ");

    }

    public function down(Schema $schema)
    {
    }
}
