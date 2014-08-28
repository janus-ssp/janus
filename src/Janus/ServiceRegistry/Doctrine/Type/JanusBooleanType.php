<?php

namespace Janus\ServiceRegistry\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Workaround type to make working with booleans easy without having to change the schema
 */
class JanusBooleanType extends Type
{
    const NAME = 'janusBoolean';

    const VALUE_TRUE = 'yes';
    const VALUE_FALSE = 'no';

    public function getName()
    {
        return static::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 3;
        $fieldDeclaration['fixed'] = true;
        $fieldDeclaration['notnull'] = true;

        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === self::VALUE_TRUE);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value === true) ? self::VALUE_TRUE : self::VALUE_FALSE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}