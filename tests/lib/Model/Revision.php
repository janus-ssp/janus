<?php
class sspmod_janus_Model_Entity_Test extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Entityid cannot be empty
     */
    public function testInstantiationWhenNameIsEmpty()
    {
        new sspmod_janus_Model_Entity(
            ''
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Entityid must be a string, instead an 'NULL' was passed
     */
    public function testInstantiationFailsWhenNameIsOfIncorrectType()
    {
        new sspmod_janus_Model_Entity(
            null
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Entityid is 256 chars long while only 255 chars are allowed
     */
    public function testInstantiationFailsWhenNameIsTooLong()
    {
        new sspmod_janus_Model_Entity(
            str_repeat('a', 256)
        );
    }
}