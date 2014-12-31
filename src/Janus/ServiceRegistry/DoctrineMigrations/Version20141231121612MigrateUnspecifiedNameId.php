<?php

namespace Janus\ServiceRegistry\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Janus\ServiceRegistry\DoctrineMigrations\Base\JanusMigration;

/**
 * Deprecate urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified.
 * @see https://github.com/OpenConext/OpenConext-engineblock/issues/96
 */
class Version20141231121612MigrateUnspecifiedNameId extends JanusMigration
{
    const LEGACY_NAMEID = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified';
    const PROPER_NAMEID = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    public function up(Schema $schema)
    {
        $prefixedTableName = $this->getTablePrefix() . 'metadata';
        $this->addSql(
            "UPDATE $prefixedTableName SET value=? WHERE value=?",
            array(self::PROPER_NAMEID, self::LEGACY_NAMEID)
        );
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException(
            'Unable to convert urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified back to'
            . ' urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'
        );
    }
}
