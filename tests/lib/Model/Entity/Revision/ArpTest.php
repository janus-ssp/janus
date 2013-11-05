<?php
class sspmod_janus_Model_Entity_Revision_ArpTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $arp = new sspmod_janus_Model_Entity_Revision_Arp(
            'fooName',
            'fooDescription',
            true,
            array(
                'attribute1'
            )
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Arp', $arp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Name '' is invalid
     */
    public function testInstantiationFailsWhenNameIsInvalid()
    {
        $arp = new sspmod_janus_Model_Entity_Revision_Arp(
            null,
            'fooDescription',
            true,
            array(
                'attribute1'
            )
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Arp', $arp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Description '' is invalid
     */
    public function testInstantiationFailsWhenDescriptionIsInvalid()
    {
        $arp = new sspmod_janus_Model_Entity_Revision_Arp(
            'fooName',
            null,
            true,
            array(
                'attribute1'
            )
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Arp', $arp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage IsDefault '' is invalid
     */
    public function testInstantiationFailsWhenDefaultIsInvalid()
    {
        $arp = new sspmod_janus_Model_Entity_Revision_Arp(
            'fooName',
            'fooDescription',
            null,
            array(
                'attribute1'
            )
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Arp', $arp);
    }
}