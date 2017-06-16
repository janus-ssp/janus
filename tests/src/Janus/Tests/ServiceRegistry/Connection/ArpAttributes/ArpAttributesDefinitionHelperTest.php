<?php

namespace src\Janus\Tests\ServiceRegistry\Connection\ArpAttributes;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use Janus\ServiceRegistry\Connection\ArpAttributes\ArpAttributesDefinitionHelper;
use PHPUnit_Framework_TestCase;

class ArpAttributesDefinitionHelperTest extends PHPUnit_Framework_TestCase
{

    public function testCanBeCreated()
    {
        $config = new ConfigProxy([]);
        $helper = new ArpAttributesDefinitionHelper($config);
        $this->assertInstanceOf(ArpAttributesDefinitionHelper::class, $helper);
    }

    public function testAppendSource()
    {
        $helper = new ArpAttributesDefinitionHelper($this->getConfigProxy());
        $inputArpAttributes = array(
            'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
                array(
                    0 => '*',
                ),
            'urn:mace:dir:attribute-def:eduPersonOrcid' =>
                array(
                    0 => '*',
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
                    0 => '*',
                    1 => 'voot'
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

    private function getConfigProxy()
    {
        return new ConfigProxy(
            [
                'attributes' => [
                    'eduPersonTargetedID' => [
                        'name' => 'urn:mace:dir:attribute-def:eduPersonTargetedID',
                    ],
                    'eduPersonOrcid' => [
                        'name' => 'urn:mace:dir:attribute-def:eduPersonOrcid',
                        'source' => 'voot',
                    ],
                    'displayName' => [
                        'name' => 'urn:mace:dir:attribute-def:displayName',
                    ],
                ]
            ]
        );
    }
} 