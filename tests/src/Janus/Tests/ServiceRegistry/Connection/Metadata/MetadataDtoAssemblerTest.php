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
        Phake::when($metadataDefinitionHelper)->castData($flatCollection, 'saml20-idp')->thenReturn($flatCollection);
        $metaDataAssembler = new MetadataDtoAssembler();
        $nestedMetadata = $metaDataAssembler->createFromFlatArray($flatCollection, $metadataDefinitionHelper, 'saml20-idp');

        $this->assertEquals(1, $nestedMetadata['foo']['bar']['baz']);
    }
}