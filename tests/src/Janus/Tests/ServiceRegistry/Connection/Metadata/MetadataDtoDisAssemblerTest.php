<?php
namespace src\Janus\Tests\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDtoDisassembler;
use PHPUnit_Framework_TestCase;

class MetadataDtoDisAssemblerTest extends PHPUnit_Framework_TestCase
{
    public function testFlattensADto()
    {
        // Create Metadata definition helper
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'foo:bar:baz' => array(
                        'supported' => array(
                            1
                        )
                    )
                )
            )
        ));
        $metadataDefinitionHelper = new MetadataDefinitionHelper($config);

        // Mock metadata dto
        $nestedMetadata = array(
            'foo' => array(
                'bar' => array(
                    'baz' => 1
                )
            )
        );

        $metadataDtoDisassembler = new MetadataDtoDisassembler($metadataDefinitionHelper);

        $expectedFlatCollection = array(
            'foo:bar:baz' => 1
        );
        $this->assertEquals($expectedFlatCollection, $metadataDtoDisassembler->flatten($nestedMetadata, 'saml20-idp'));
    }
} 