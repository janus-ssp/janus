<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Doctrine\Type;

use DateTime;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class JanusDateTimeType extends Type
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
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        $timeStamp = strtotime($value);
        if ($timeStamp === false) {
            return null;
        }

        $datetime = new DateTime();
        $datetime->setTimestamp($timeStamp);
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

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}