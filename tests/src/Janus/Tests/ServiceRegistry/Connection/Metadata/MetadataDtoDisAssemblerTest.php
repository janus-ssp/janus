<?php
namespace src\Janus\Tests\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\Connection\Metadata\MetadataDto;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDtoDisassembler;
use PHPUnit_Framework_TestCase;
use Phake;

class MetadataDtoDisAssemblerTest extends PHPUnit_Framework_TestCase
{
    public function testFlattensADto()
    {
        // Mock metadata dto
        $dto = new MetadataDto(
            array(
                'foo' => array(
                    'bar' => array(
                        'baz' => 1
                    )
                )
            )
        );
        $metadataDefinitionHelper = Phake::mock('Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'foo', false)->thenReturn('foo');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'bar', false)->thenReturn('foo:bar');
        Phake::when($metadataDefinitionHelper)->joinKeyParts(null, 'baz', false)->thenReturn('foo:bar:baz');
        $metadataDtoDisassembler = new MetadataDtoDisassembler($metadataDefinitionHelper);

        $expectedFlatCollection = array(
            'foo:bar:baz' => 1
        );
        $this->assertEquals($expectedFlatCollection, $metadataDtoDisassembler->flatten($dto));
    }
} 