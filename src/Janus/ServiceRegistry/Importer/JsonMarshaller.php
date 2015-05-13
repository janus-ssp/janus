<?php

namespace Janus\ServiceRegistry;

use Janus\ServiceRegistry\Entity\Connection\Revision;

class JsonMarshaller
{
    public function marshall(Revision $revision)
    {
        return $revision;
    }
}
