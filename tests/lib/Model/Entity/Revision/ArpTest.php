<?php
class sspmod_janus_Model_Entity_Revision_ArpTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $arp = new sspmod_janus_Model_Entity_Revision_Arp();

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision_Arp', $arp);
    }
}