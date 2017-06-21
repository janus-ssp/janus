<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Tests\Service;

use Janus\ServiceRegistry\Bundle\CoreBundle\Service\ArpAttributeHelper;
use PHPUnit_Framework_TestCase;

class ArpAttributeHelperTest extends PHPUnit_Framework_TestCase
{
    public function testCanBeCreated()
    {
        $this->assertInstanceOf(
            '\Janus\ServiceRegistry\Bundle\CoreBundle\Service\ArpAttributeHelper',
            $this->getHelper()
        );
    }

    public function testMergeAttributesCanHanldeEmptyInput()
    {
        $merged = $this->getHelper()->mergeAttributes(array(), array());
        $this->assertEmpty($merged);
    }

    public function testMergeAttributesWithSources()
    {
        $attrPosted = array(
            'urn:mace:dir:attribute-def:mail' => array('*', 'urn:foobar:test:*'),
            'urn:schac:attribute-def:schacPersonalUniqueCode' => array('*'),
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => array('*'),
            'urn:mace:dir:attribute-def:uid' => array('*'),
        );
        $sourcesPosted = array(
            'urn:mace:dir:attribute-def:mail' => 'voot',
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => 'idp',
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'voot',
            'urn:mace:dir:attribute-def:uid' => 'sab',
        );

        $expectedOutput = array(
            'urn:mace:dir:attribute-def:mail' => array(
                array(
                    'value' => '*',
                    'source' => 'voot',
                ),
                array(
                    'value' => 'urn:foobar:test:*',
                    'source' => 'voot',
                ),
            ),
            'urn:schac:attribute-def:schacPersonalUniqueCode' => array(
                array(
                    'value' => '*',
                ),
            ),
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => array(
                array(
                    'value' => '*',
                    'source' => 'voot',
                ),
            ),
            'urn:mace:dir:attribute-def:uid' => array(
                array(
                    'value' => '*',
                    'source' => 'sab',
                ),
            ),
        );

        $merged = $this->getHelper()->mergeAttributes($attrPosted, $sourcesPosted);
        $this->assertEquals($expectedOutput, $merged);
    }

    public function testMergeAttributesWithoutSources()
    {
        $attrPosted = array(
            'urn:mace:dir:attribute-def:mail' => array('*', 'urn:foobar:test:*'),
            'urn:schac:attribute-def:schacPersonalUniqueCode' => array('*'),
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => array('*'),
            'urn:mace:dir:attribute-def:uid' => array('*'),
        );
        $sourcesPosted = array(
            'urn:mace:dir:attribute-def:mail' => 'idp',
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => 'idp',
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'idp',
            'urn:mace:dir:attribute-def:uid' => 'idp',
        );

        $expectedOutput = array(
            'urn:mace:dir:attribute-def:mail' => array(
                array(
                    'value' => '*',
                ),
                array(
                    'value' => 'urn:foobar:test:*',
                ),
            ),
            'urn:schac:attribute-def:schacPersonalUniqueCode' => array(
                array(
                    'value' => '*',
                ),
            ),
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => array(
                array(
                    'value' => '*',
                ),
            ),
            'urn:mace:dir:attribute-def:uid' => array(
                array(
                    'value' => '*',
                ),
            ),
        );

        $merged = $this->getHelper()->mergeAttributes($attrPosted, $sourcesPosted);
        $this->assertEquals($expectedOutput, $merged);
    }

    private function getHelper()
    {
        return new ArpAttributeHelper();
    }
}
