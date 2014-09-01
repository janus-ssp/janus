<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715061939AddPrimaryKeys extends JanusMigration
{
    /**
     * Adds Primary key to each column (doctrine requires this)
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Note that the ignore statement removes duplicate entries (which should not be there in the first place)
        $this->addSql("
            ALTER IGNORE TABLE " . $this->getTablePrefix() . "hasConnection
                CHANGE `eid` `eid` INT(11) NOT NULL,
                ADD PRIMARY KEY (uid, eid)");

        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "userData
                DROP INDEX uid,
                ADD PRIMARY KEY (uid, `key`)");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "hasConnection
                DROP PRIMARY KEY,
                CHANGE eid eid int(11) DEFAULT NULL");

        $this->addSql("
            ALTER TABLE " . $this->getTablePrefix() . "userData
                DROP PRIMARY KEY,
                ADD UNIQUE INDEX uid (uid, `key`)");
    }
}
