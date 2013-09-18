<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;

class Version20130715123301 extends AbstractMigration
{
    private $tablePrefix = 'janus__';

    /**
     * @param Schema $schema
     *
     * Change user type to a varchar since a text is REALLY overkill, also it should not be nullable
     */
    public function up(Schema $schema)
    {
        $schema->getTable($this->tablePrefix . 'user')
            ->changeColumn('type', array(
                'type' => Type::getType(TYPE::STRING),
                'length' => 255,
                'notnull' => true,
                'default' => null
            ));

    }

    public function down(Schema $schema)
    {
        $schema->getTable($this->tablePrefix . 'user')
            ->changeColumn('type', array(
                'type' => Type::getType(TYPE::TEXT),
                'notnull' => false,
                'default' => null
            ));
    }
}