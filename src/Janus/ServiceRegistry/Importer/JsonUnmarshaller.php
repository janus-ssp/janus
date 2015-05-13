<?php


namespace Janus\ServiceRegistry;


use Janus\ServiceRegistry\Entity\Connection\Revision;

class JsonUnmarshaller
{
    public function unmarshall($jsonString)
    {
        $revision = new Revision();
        return $revision;
    }
}
