<?php

namespace src\Janus\Tests\ServiceRegistry\Connection\Metadata;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\Metadata\MetadataDefinitionHelper;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Translation\Tests\MessageCatalogueTest;

class MetadataDefinitionHelperTest extends PHPUnit_Framework_TestCase
{
    public function testCastsBooleanValues()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'booleanField' => array(
                        'type' => 'boolean'
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);
        
        $valuesToCast = array(
            'booleanField' => 1
        );
        $expectedCastedValues = array(
            'booleanField' => true
        );
        $this->assertEquals($expectedCastedValues, $helper->castData($valuesToCast));
    }

    public function testDoesNotCastOtherValues()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'uncastableField' => array(
                        'type' => 'string'
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $valuesToCast = array(
            'uncastableField' => 'foo'
        );
        $expectedCastedValues = array(
            'uncastableField' => 'foo'
        );
        $this->assertEquals($expectedCastedValues, $helper->castData($valuesToCast));
    }

    public function testSkipsCastingUnknownFields()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array()
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $valuesToCast = array(
            'unknownField' => 'foo'
        );
        $expectedCastedValues = array(
            'unknownField' => 'foo'
        );
        $this->assertEquals($expectedCastedValues, $helper->castData($valuesToCast));
    }

    public function testJoinsJustSubkeyIfNoParentKeyWasGiven()
    {
        $config = new ConfigProxy(array());
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $this->assertEquals('foo', $helper->joinKeyParts(null, 'foo'));
    }

    public function testJoinsParentAndSubKeyKWithDoubleColon()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'foo:bar' => array(
                        'type' => 'string'
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $this->assertEquals('foo:bar', $helper->joinKeyParts('foo', 'bar'));
    }

    public function testJoinsParentAndSubKeyWithDot()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'foo.bar' => array(
                        'type' => 'string'
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $this->assertEquals('foo.bar', $helper->joinKeyParts('foo', 'bar'));
    }

    public function testJoinsParentAndSubKeyWithDoubleColonWhenDefaultShouldBeProvided()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'baz' => array(
                        'type' => 'string'
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $this->assertEquals('foo:bar', $helper->joinKeyParts('foo', 'bar', true));
    }

    public function testJoinsSupportedValues()
    {
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                    'foo' => array(
                        'supported' => array(
                            'bar'
                        )
                    )
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $this->assertEquals('foo:bar', $helper->joinKeyParts('foo', 'bar', true));
    }

    public function testThrowsExceptionWhenFieldIsUnknown()
    {
        $expectedExceptionMessage = <<<MESSAGE
Unable to find proper separator for 'foo' 'bar' (tried foo:bar and foo.bar. Perhaps the definition is missing?
MESSAGE;

        $this->setExpectedException('RuntimeException', $expectedExceptionMessage);

        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(
                )
            )
        ));
        $helper = new MetadataDefinitionHelper('saml20-idp', $config);

        $helper->joinKeyParts('foo', 'bar');
    }
} 