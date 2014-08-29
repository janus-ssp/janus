<?php

namespace Janus\ServiceRegistry\Connection\Metadata\MetadataDto\Assembler;

use Doctrine\Common\Collections\Collection;

interface AssemblerInterface
{
    public function assemble(Collection $metadata);
}