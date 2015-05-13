<?php

namespace Janus\ServiceRegistry;

class UnmarshallerFactory {
    public function createForType($type)
    {
        if ($type === 'json') {
            return new JsonUnmarshaller();
        }
        if ($type === 'xml') {
            return new SamlMdUnmarshaller();
        }

        throw new \RuntimeException("Unknown type: " . $type);
    }
}
