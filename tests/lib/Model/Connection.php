<?php
class sspmod_janus_Model_Connection_Test extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Name cannot be empty
     */
    public function testInstantiationWhenNameIsEmpty()
    {
        new sspmod_janus_Model_Connection(
            ''
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Name must be a string, instead an 'NULL' was passed
     */
    public function testInstantiationFailsWhenNameIsOfIncorrectType()
    {
        new sspmod_janus_Model_Connection(
            null
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Name is 256 chars long while only 255 chars are allowed
     */
    public function testInstantiationFailsWhenNameIsTooLong()
    {
        new sspmod_janus_Model_Connection(
            str_repeat('a', 256)
        );
    }
}