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

    public function testAddDefaultAttributeSource()
    {
        $input = array('mySource', 'yourSource');
        $expectedOutput = array(ArpAttributeHelper::ARP_DEFAULT_SOURCE, 'mySource', 'yourSource');
        $this->assertEquals($expectedOutput, $this->getHelper()->addDefaultAttributeSource($input));
    }

    public function testAddDefaultAttributeSourceEmptyInput()
    {
        $expectedOutput = array(ArpAttributeHelper::ARP_DEFAULT_SOURCE);
        $this->assertEquals($expectedOutput, $this->getHelper()->addDefaultAttributeSource(array()));
    }

    public function testGetSelectedSourceUnableToFindSource()
    {
        $input = array(
            array (
                'value' => '*',
            ),
            array (
                'value' => 'specifc_filter',
            ),
        );
        $selectedSource = $this->getHelper()->getSelectedSource($input);
        $this->assertEquals(ArpAttributeHelper::ARP_DEFAULT_SOURCE, $selectedSource);
    }

    public function testGetSelectedSource()
    {
        $input = array(
            array (
                'value' => '*',
                'source' => 'voot',
            ),
            array (
                'value' => 'specifc_filter',
                'source' => 'voot',
            ),
        );
        $selectedSource = $this->getHelper()->getSelectedSource($input);
        $this->assertEquals('voot', $selectedSource);
    }

    /**
     * If somehow the source differs between the atrribute values, the default value is returned. This is an edge
     * case that should not occur.
     */
    public function testGetSelectedSourceMultipleSourcesReturnsDefault()
    {
        $input = array(
            array (
                'value' => '*',
                'source' => 'voot',
            ),
            array (
                'value' => 'specifc_filter',
                'source' => 'sab',
            ),
        );
        $selectedSource = $this->getHelper()->getSelectedSource($input);
        $this->assertEquals(ArpAttributeHelper::ARP_DEFAULT_SOURCE, $selectedSource);
    }

    private function getHelper()
    {
        return new ArpAttributeHelper();
    }
}
