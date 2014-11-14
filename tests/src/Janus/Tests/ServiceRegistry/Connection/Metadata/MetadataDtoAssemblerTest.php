<?php

namespace src\Janus\Tests\ServiceRegistry\Connection;

use Phake;
use PHPUnit_Framework_TestCase;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDtoAssembler;

class MetadataDtoAssemblerTest extends PHPUnit_Framework_TestCase
{
    public function testCreatesNestedInstanceFromFlatArray()
    {
        $flatCollection = array(
            'foo:bar:baz' => 1
        );

        $metadataDefinitionHelper = Phake::mock('Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper');
        Phake::when($metadataDefinitionHelper)->castData($flatCollection)->thenReturn($flatCollection);
        $metaDataAssembler = new MetadataDtoAssembler();
        $metadataDto = $metaDataAssembler->createFromFlatArray($flatCollection, $metadataDefinitionHelper);

        $this->assertEquals(1, $metadataDto['foo']['bar']['baz']);
    }
}