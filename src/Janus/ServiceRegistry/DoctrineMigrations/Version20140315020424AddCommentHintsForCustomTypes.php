<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140315020424AddCommentHintsForCustomTypes extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "connectionRevision
            CHANGE expiration expiration CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE allowedall allowedall CHAR(25) DEFAULT 'yes' NOT NULL COMMENT '(DC2Type:janusBoolean)',
            CHANGE created created CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)',
            CHANGE active active CHAR(3) DEFAULT 'yes' NOT NULL COMMENT '(DC2Type:janusBoolean)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "blockedConnection
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) NOT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "allowedConnection
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) NOT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "disableConsent
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) NOT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "metadata
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) NOT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "userData
            CHANGE `update` `update` CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) NOT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "message
            CHANGE `read` `read` CHAR(25) DEFAULT 'no' NOT NULL COMMENT '(DC2Type:janusBoolean)',
            CHANGE created created CHAR(25) NOT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "hasConnection
            CHANGE created created CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "subscription
            CHANGE created created CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "connection
            CHANGE created created CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)'");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "user
            CHANGE type type VARCHAR(255) NOT NULL COMMENT '(DC2Type:janusUserType)',
            CHANGE active active CHAR(3) DEFAULT 'yes' COMMENT '(DC2Type:janusBoolean)',
            CHANGE `update` `update` CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE created created CHAR(25) DEFAULT NULL COMMENT '(DC2Type:janusDateTime)',
            CHANGE ip ip CHAR(39) DEFAULT NULL COMMENT '(DC2Type:janusIp)'");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "allowedConnection 
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) NOT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "blockedConnection 
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) NOT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "connection 
            CHANGE created created CHAR(25) DEFAULT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "connectionRevision 
            CHANGE expiration expiration CHAR(25) DEFAULT NULL,
            CHANGE allowedall allowedall CHAR(3) DEFAULT 'yes' NOT NULL,
            CHANGE created created CHAR(25) DEFAULT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL,
            CHANGE active active CHAR(3) DEFAULT 'yes' NOT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "disableConsent 
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) NOT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "hasConnection 
            CHANGE created created CHAR(25) DEFAULT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "message 
            CHANGE `read` `read` CHAR(3) DEFAULT 'no' NOT NULL,
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "metadata 
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) NOT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "subscription 
            CHANGE created created CHAR(25) DEFAULT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "user 
            CHANGE type type VARCHAR(255) NOT NULL,
            CHANGE active active CHAR(3) DEFAULT 'yes',
            CHANGE `update` `update` CHAR(25) DEFAULT NULL,
            CHANGE created created CHAR(25) DEFAULT NULL,
            CHANGE ip ip CHAR(39) DEFAULT NULL");
        $this->addSql("ALTER TABLE " . $this->getTablePrefix() . "userData 
            CHANGE `update` `update` CHAR(25) NOT NULL,
            CHANGE created created CHAR(25) NOT NULL,
            CHANGE ip ip CHAR(39) NOT NULL");
    }
}
