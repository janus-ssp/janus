<?php

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @todo add mapping to and from DateTime Objects
 */
class sspmod_janus_Doctrine_Type_JanusDateTimeType extends StringType
{
    const NAME = 'janusDateTime';

    public function getName()
    {
        return static::NAME;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 25;
        $fieldDeclaration['fixed'] = true;
        $fieldDeclaration['notnull'] = true;

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        $datetime = new DateTime();
        $datetime->setTimestamp(strtotime($value));
        return $datetime;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // @todo make date columns nullable
        if (is_string($value) && empty($value)) {
            return '';
        }

        if ($value instanceof DateTime) {
            return $value->format(DateTime::ATOM);
        }
    }
}