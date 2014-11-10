<?php

namespace src\Janus\Tests\ServiceRegistry\Connection;


use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use PHPUnit_Framework_TestCase;
use Phake;

class MetadataDtoTest extends PHPUnit_Framework_TestCase
{

    public function testCreatesNestedInstanceFromFlatArray()
    {
        $flatCollection = array(
            'foo:bar:baz' => 1

        );

        $metadataDefinitionHelper = Phake::mock('Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper');
        Phake::when($metadataDefinitionHelper)->castData($flatCollection)->thenReturn($flatCollection);
        $metadataDto = MetadataDto::createFromFlatArray($flatCollection, $metadataDefinitionHelper);

        $this->assertEquals(1, $metadataDto['foo']['bar']['baz']);
    }
}