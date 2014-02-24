<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Doctrine\Type;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Workaround type to make working with booleans easy without having to change the schema
 */
class JanusBooleanType extends StringType
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

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === self::VALUE_TRUE);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value === true) ? self::VALUE_TRUE : self::VALUE_FALSE;
    }
}