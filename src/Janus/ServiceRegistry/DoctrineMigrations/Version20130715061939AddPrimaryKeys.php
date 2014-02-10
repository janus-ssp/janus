<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20130715061939AddPrimaryKeys extends AbstractMigration
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
            ALTER IGNORE TABLE " . DB_TABLE_PREFIX . "hasConnection
                CHANGE `eid` `eid` INT(11) NOT NULL,
                ADD PRIMARY KEY (uid, eid)");

        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "userData
                DROP INDEX uid,
                ADD PRIMARY KEY (uid, `key`)");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "hasConnection
                DROP PRIMARY KEY,
                CHANGE eid eid int(11) DEFAULT NULL");

        $this->addSql("
            ALTER TABLE " . DB_TABLE_PREFIX . "userData
                DROP PRIMARY KEY,
                ADD UNIQUE INDEX uid (uid, `key`)");
    }
}
