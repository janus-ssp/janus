<?php

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Type to make working with collections of user types stored with a user easy
 */
class sspmod_janus_Doctrine_Type_JanusUserTypeType extends StringType
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

        return parent::getSqlDeclaration($fieldDeclaration, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return unserialize($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return serialize($value);
    }
}