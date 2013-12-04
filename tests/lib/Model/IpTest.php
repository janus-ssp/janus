<?php
class sspmod_janus_Model_IpTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $user = new sspmod_janus_Model_Ip(
            '127.0.0.1'
        );

        $this->assertInstanceOf('sspmod_janus_Model_Ip', $user);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  IP '123' is not a valid address
     */
    public function testInstantiationFailsWithInvalidAddress()
    {
        new sspmod_janus_Model_Ip(
            123
        );
    }
}