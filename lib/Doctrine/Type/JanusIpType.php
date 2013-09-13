<?php

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class sspmod_janus_Doctrine_Type_JanusIpType extends StringType
{
    const NAME = 'janusIp';

    public function getName()
    {
        return static::NAME;
    }

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 39;
        $fieldDeclaration['fixed'] = true;

        return parent::getSqlDeclaration($fieldDeclaration, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        return new sspmod_janus_Model_Ip($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof sspmod_janus_Model_Ip) {
            return (string) $value;
        }
    }
}