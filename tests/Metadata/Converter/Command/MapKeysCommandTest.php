<?php

class sspmod_janus_Metadata_Converter_Command_MapKeysCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var sspmod_janus_Metadata_Converter_Command_MapKeysCommand */
    private $mapKeysCommand;

    public function setUp()
    {
        $this->mapKeysCommand = new sspmod_janus_Metadata_Converter_Command_MapKeysCommand();
    }

    /**
     * @dataProvider provider
     */
    public function testMapping($data, $result, $mapping)
    {
        $this->mapKeysCommand->setMapping($mapping);
        $this->assertEquals($result, $this->mapKeysCommand->convert($data));
    }

    public function provider()
    {
        return array(
            array(
                array("foo" => "bar", "foobar" => "baz"),
                array("bar" => "bar", "foobar" => "baz"),
                array("foo" => "bar"),
            ),
        );
    }
}
