<?php

namespace src\Janus\Tests\ServiceRegistry\Connection\ArpAttributes;

use Janus\ServiceRegistry\Connection\ArpAttributes\ArpAttributesDefinitionHelper;
use PHPUnit_Framework_TestCase;

class ArpAttributesDefinitionHelperTest extends PHPUnit_Framework_TestCase
{

    public function testCanBeCreated()
    {
        $helper = new ArpAttributesDefinitionHelper();
        $this->assertInstanceOf(ArpAttributesDefinitionHelper::class, $helper);
    }

    public function testAppendSource()
    {
        $helper = new ArpAttributesDefinitionHelper();
        $inputArpAttributes = array(
            'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
                array(
                    0 => '*',
                ),
            'urn:mace:dir:attribute-def:eduPersonOrcid' =>
                array(
                    0 => '*',
                    1 => 'urn:mace:foobar:*'
                ),
            'urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer' =>
                array(
                    0 => '*',
                ),
            'urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier' =>
                array(
                    0 => '*',
                ),
        );

        $expectedArpAttributes = array(
            'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
                array(
                    0 => '*',
                ),
            'urn:mace:dir:attribute-def:eduPersonOrcid' =>
                array(
                    0 => [
                        'value' => '*',
                        'source' => 'voot'
                    ],
                    1 => [
                        'value' => 'urn:mace:foobar:*',
                        'source' => 'voot'
                    ]
                ),
            'urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer' =>
                array(
                    0 => '*',
                ),
            'urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier' =>
                array(
                    0 => '*',
                ),
        );
        $arpAttributes = $helper->appendSource($inputArpAttributes);

        $this->assertEquals($expectedArpAttributes, $arpAttributes);
    }

    public function testAppendEmptySource()
    {
        $helper = new ArpAttributesDefinitionHelper();
        $arpAttributes = $helper->appendSource('');
        $this->assertEmpty($arpAttributes);
    }
} 