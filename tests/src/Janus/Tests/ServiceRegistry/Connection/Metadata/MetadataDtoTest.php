<?php

namespace src\Janus\Tests\ServiceRegistry\Connection\Metadata;


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

    public function testFlattensItself()
    {
        // Mock metadata dto
        $items = array(
            'foo' => array(
                'bar' => array(
                    'baz' => 1
                )
            )
        );
        $metadataDefinitionHelper = Phake::mock('Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'foo', false)->thenReturn('foo');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'bar', false)->thenReturn('foo:bar');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'baz', false)->thenReturn('foo:bar:baz');
        $metadataDto = new MetadataDto($items, $metadataDefinitionHelper);

        $expectedFlatCollection = array(
            'foo:bar:baz' => 1
        );
        $this->assertEquals($expectedFlatCollection, $metadataDto->flatten());
    }
}