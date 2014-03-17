<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\ServiceRegistry\Doctrine\Type;

use Janus\ServiceRegistry\Value\Ip;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class JanusIpType extends Type
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
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        // If stored ip is invalid no not return it.
        // It will be overwritten or corrected in a new revision by the audit properties updater anyway.
        try {
            return new Ip($value);
        } catch(\InvalidArgumentException $ex) {
            return null;
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Ip) {
            return (string) $value;
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}