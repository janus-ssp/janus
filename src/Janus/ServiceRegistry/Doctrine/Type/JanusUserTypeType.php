<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Type to make working with collections of user types stored with a user easy
 */
class JanusUserTypeType extends Type
{
    const NAME = 'janusUserType';

    public function getName()
    {
        return static::NAME;
    }

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 255;
        $fieldDeclaration['notnull'] = false;
        $fieldDeclaration['default'] = null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $phpValue = @unserialize($value);

        if ($phpValue === false) {
            return null;
        }

        return $phpValue;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return serialize($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}